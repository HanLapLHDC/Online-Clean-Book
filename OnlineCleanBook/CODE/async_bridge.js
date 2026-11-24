'use strict';

(() => {
  const config = window.MOCBOOKS_ASYNC_CONFIG || {};
  const endpoint = config.endpoint || 'async_api.php';
  const csrfToken = config.csrfToken || '';
  const queueKey = 'mocbooks_async_event_queue_v1';
  const maxQueueSize = 50;
  const syncEventName = 'mocbooks:async-sync';
  let flushPromise = null;
  let flushTimer = null;
  let retryDelay = 1500;

  function readJson(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      return raw === null ? fallback : JSON.parse(raw);
    } catch (_) {
      return fallback;
    }
  }

  function readQueue() {
    const queue = readJson(queueKey, []);
    return Array.isArray(queue) ? queue : [];
  }

  function writeQueue(queue) {
    try {
      localStorage.setItem(queueKey, JSON.stringify(queue.slice(-maxQueueSize)));
    } catch (_) {
      // Storage may be disabled or full. The request can still be attempted once.
    }
  }

  function currentMetrics() {
    const db = readJson('mocbooks_storefront_db_v3', null);
    const cart = readJson('mocbooks_sql_cp02_cart_v2', []);
    const safeCart = Array.isArray(cart) ? cart : [];
    return {
      cart_lines: safeCart.length,
      cart_quantity: safeCart.reduce((sum, item) => sum + Math.max(0, Number(item?.quantity) || 0), 0),
      book_count: Array.isArray(db?.books) ? db.books.length : 0
    };
  }

  function currentRoute() {
    const route = decodeURIComponent((window.location.hash || '#home').slice(1));
    return /^(home|catalog|cart|checkout|success|admin)$/.test(route) || /^detail\/\d+$/.test(route)
      ? route
      : 'home';
  }

  function showStatus(message, isError = false) {
    let badge = document.getElementById('async-sync-status');
    if (!badge) {
      badge = document.createElement('div');
      badge.id = 'async-sync-status';
      badge.setAttribute('role', 'status');
      badge.setAttribute('aria-live', 'polite');
      badge.style.cssText = [
        'position:fixed', 'right:16px', 'bottom:16px', 'z-index:999',
        'padding:9px 13px', 'border-radius:999px', 'font-size:12px',
        'font-weight:700', 'background:#174c3d', 'color:#fff',
        'box-shadow:0 8px 24px rgba(20,45,36,.18)',
        'opacity:0', 'transition:opacity .2s ease', 'pointer-events:none'
      ].join(';');
      document.body.appendChild(badge);
    }
    badge.style.background = isError ? '#8a332d' : '#174c3d';
    badge.textContent = message;
    badge.style.opacity = '1';
    clearTimeout(showStatus.timer);
    showStatus.timer = setTimeout(() => { badge.style.opacity = '0'; }, 2400);
  }

  function enqueue(eventName) {
    const queue = readQueue();
    queue.push({
      event: eventName,
      route: currentRoute(),
      metrics: currentMetrics(),
      queued_at: new Date().toISOString()
    });
    writeQueue(queue.slice(-maxQueueSize));
    showStatus('Async: Đang gửi sự kiện...');
    flushQueue();
  }

  async function flushQueue() {
    if (flushPromise) return flushPromise;
    flushPromise = (async () => {
      if (!csrfToken) {
        showStatus('Async: Thiếu session token', true);
        return;
      }

      let queue = readQueue();
      while (queue.length > 0) {
        const item = queue[0];
        try {
          const response = await fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(item),
            cache: 'no-store'
          });
          const result = await response.json().catch(() => ({}));
          if (!response.ok || result.ok !== true || result.stored !== true) {
            throw new Error(result.message || `HTTP ${response.status}`);
          }

          queue.shift();
          writeQueue(queue);
          window.dispatchEvent(new CustomEvent(syncEventName, { detail: result }));
          showStatus(`Async: Đã ghi MySQL (#${result.event_id})`);
          retryDelay = 1500;
        } catch (error) {
          showStatus('Async: Đang chờ kết nối máy chủ', true);
          clearTimeout(flushTimer);
          flushTimer = setTimeout(flushQueue, retryDelay);
          retryDelay = Math.min(retryDelay * 2, 30000);
          break;
        }
        queue = readQueue();
      }
    })().finally(() => {
      flushPromise = null;
    });
    return flushPromise;
  }

  function track(eventName) {
    if (!eventName) return;
    enqueue(eventName);
  }

  window.addEventListener('DOMContentLoaded', () => track('boot'), { once: true });
  window.addEventListener('hashchange', () => track('route-change'));
  window.addEventListener('online', () => {
    clearTimeout(flushTimer);
    track('online-retry');
  });

  document.addEventListener('click', event => {
    if (event.target instanceof Element && event.target.closest('[data-action]')) {
      track('action-click');
    }
  });

  document.addEventListener('change', event => {
    if (event.target instanceof HTMLSelectElement && event.target.id === 'catalog-sort') {
      track('sort-change');
    }
  });

  document.addEventListener('submit', event => {
    const formId = event.target?.id;
    const map = {
      'checkout-form': 'checkout-submit',
      'book-form': 'book-form-submit',
      'category-form': 'category-form-submit'
    };
    if (map[formId]) track(map[formId]);
  });

  window.MocBooksAsync = {
    track,
    syncNow: () => track('manual-sync'),
    pendingCount: () => readQueue().length,
    flush: flushQueue
  };

  // Attempt to send any events retained from an earlier offline session.
  if (readQueue().length > 0) {
    window.addEventListener('load', () => flushQueue(), { once: true });
  }
})();
