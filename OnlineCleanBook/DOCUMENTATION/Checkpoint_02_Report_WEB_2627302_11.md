## ONLINE CLEAN BOOK

## WEB PROGRAMMING - CHECKPOINT 02

## I. PROJECT INFORMATION

## 1.1. System Overview

Mộc Books is an online bookstore prototype that helps visitors discover books, browse categories, review product information, manage a shopping cart, and explore the checkout process. The storefront displays book title, author, regular price, member price, available stock, image reference, and description.

The application uses PHP and PDO to connect to the MySQL database bookstore_dev. The PHP entry point (index.php) retrieves the initial categories, books, users, orders, and order_items records. JavaScript (app.js) renders the storefront and handles the current browser interactions, while styles.css provides the responsive visual layout. The additional asynchronous module (async_bridge.js and async_api.php) records selected interface events in the async_events table.

## 1.2. Project Objectives

- Provide a database-loaded catalog of books and categories.

- Demonstrate the customer journey from browsing and product details to cart review and checkout.

- Represent guest, regular-customer, and member-customer pricing scenarios.

- Provide administrator screens for book, category, and order management.

- Maintain a relational MySQL design for catalog, users, orders, order items, and asynchronous interaction logs.

- Demonstrate at least one asynchronous browser-to-server interaction using Fetch API, PHP, and MySQL.

## 1.3. Project Scope and Current Limitations

The current checkpoint includes the PHP/MySQL catalog read path, the browser-based storefront prototype, role and member-price demonstrations, administrator interface screens, and a working asynchronous event-logging path. In the current prototype snapshot, cart, checkout, order updates, book/category CRUD, and the selected demo role are primarily maintained in localStorage. Those browser-side demonstrations must not be described as server-persisted business transactions.

Real customer authentication with PHP sessions, server-side role authorization, database-persistent order creation, stock transactions, and database-persistent administrator CRUD remain integration tasks for the team. The QR payment panel is informational only; the application does not verify bank transfers or automatically mark an order as paid.


## II. ACTORS AND FUNCTIONAL REQUIREMENTS

## 2.1. System Actors

| Actor | Description | Responsibilities |
| --- | --- | --- |
| Guest | A visitor who has not selected a customer account. | Browse and search books, view details, manage a cart, and enter contact/delivery details in the guest checkout prototype. |
| Customer | A regular registered customer record (role = customer). | Browse books and use the regular- price shopping demonstration. Real account login/session handling remains a target requirement. |
| Member Customer | A customer record with users.is_member = TRUE. | See member_price in the current role-selection demonstration. Server-side membership authorization is not yet implemented. |
| Administrator | A user record with role = admin. | Access the administrator prototype screens for books, categories, and orders. Server-side authorization and persistence remain to be integrated. |

## 2.2. Functional Requirements

Priority: Must = core Checkpoint 02 requirement; Should = supporting requirement. Status refers to the supplied Herd-ready prototype snapshot, not to unsubmitted changes that may exist in the team DATA folder.

| ID | Requirement | Description | Actor | Priority / current status |
| --- | --- | --- | --- | --- |
| FR-01 | Browse book catalog | Retrieve and display books from books, including title, author, prices, stock, and image reference. | Guest / Customer | Must initial PHP/MySQL read path implemented. |
| FR-02 | Browse categories | Retrieve categories and display books by category_id. | Guest / Customer | Must initial PHP/MySQL read path implemented. |
| FR-03 | Search and filter | Search by title/author and filter the loaded catalog by category. | Guest / Customer | Should client-side prototype. |
| FR-04 | View book details | Display selected book description, author, prices, and stock. | Guest / Customer | Must rendered from loaded catalog data. |
| FR-05 | Manage shopping cart | Add items, change quantities, remove items, and review totals. | Guest / Customer | Must browser localStorage prototype. |


| FR-06 | Guest checkout | Collect guest name, email, phone, and delivery address without account creation. | Guest | Must browser prototype; order persistence pending. |
| --- | --- | --- | --- | --- |
| FR-07 | Customer login | Authenticate email/password using password_hash and establish a secure session. | Customer | Must not implemented; current UI selects a demo role. |
| FR-08 | Apply member price | Use member_price only for an authenticated member; otherwise use regular price. | Customer / Member | Must price display is demonstrated client-side; server-side enforcement pending. |
| FR-09 | Calculate order total | Calculate total from item quantities and applicable unit prices. | Guest / Customer | Must client-side prototype calculation. |
| FR-10 | Create order | Persist orders and corresponding order_items records. | Guest / Customer | Must currently represented in localStorage; MySQL write pending. |
| FR-11 | Maintain stock | Validate stock and reduce stock after successful order creation. | System | Must browser- state demonstration; transactional MySQL update pending. |
| FR-12 | View order list/details | Allow administrator to review orders and associated order items. | Administrator | Must administrator prototype reads the loaded data. |
| FR-13 | Update order status | Change and persist the status of an order. | Administrator | Must currently browser-state only; MySQL update pending. |
| FR-14 | Manage books | Add, edit, and delete book records, including category, price, and stock. | Administrator | Must UI/client-side prototype; DB CRUD pending. |
| FR-15 | Manage categories | Add, edit, and delete category records. | Administrator | Must UI/client-side prototype; DB CRUD pending. |
| FR-16 | Protect administrator functions | Restrict admin operations to authenticated users with role = admin. | Administrator / System | Must client- side role check only; server- side authorization pending. |


| FR-17 | Record asynchronous UI events | Send selected interface events to a PHP endpoint and store event metrics in async_events. | Guest / Customer / Administrator | Must implemented and verified on Herd (HTTP 200; 22 rows observed). |
| --- | --- | --- | --- | --- |


## III. USE-CASE MODEL

## 3.1. Use-Case Diagram

The use-case model captures the main customer and administrator goals. Member Customer is a specialized Customer actor. UC-12 is a technical supporting use case for asynchronous interaction logging; it does not create orders or change catalog records.

*Figure 1. Mộc Books use-case diagram, including asynchronous interaction logging.*

## 3.2. Use-Case Summary

| Use Case | Name | Primary Actor | Brief Description |
| --- | --- | --- | --- |
| UC-01 | Browse books | Guest, Customer | Load and browse the book catalog. |
| UC-02 | Search/filter books | Guest, Customer | Search by keyword or filter by category. |


| UC-03 | View book details | Guest, Customer | View description, author, price, member price, and stock. |
| --- | --- | --- | --- |
| UC-04 | Manage shopping cart | Guest, Customer | Add, update, remove items, and review subtotal/total. |
| UC-05 | Guest checkout | Guest | Enter recipient and delivery/contact information. |
| UC-06 | Customer login | Customer, Administrator | Target secure sign-in; current prototype uses demo role selection. |
| UC-07 | Apply member price | Member Customer | Display member_price for the selected member demo account. |
| UC-08 | Place order | Guest, Customer | Validate cart/stock, calculate total, and show an order confirmation prototype. |
| UC-09 | Manage books | Administrator | Open book management and demonstrate add/edit/delete screens. |
| UC-10 | Manage categories | Administrator | Open category management and demonstrate add/edit/delete screens. |
| UC-11 | Manage orders | Administrator | Review order list/details and demonstrate status changes. |
| UC-12 | Record UI events asynchronously | All active roles | Queue UI event metadata, send it to async_api.php, and log it in async_events. |


## IV. MAIN USE-CASE SPECIFICATIONS

## 4.1. UC-08 Place Order (Guest or Customer)

Primary actor: Guest or Customer. Preconditions: the shopping cart contains at least one book, and each selected quantity is a positive integer.

Target main flow:

- 1. The user opens the checkout page and reviews the selected items.

- 2. A guest enters name, email, phone number, and delivery address. A signed-in customer uses their account identity and supplies delivery details as required.

- 3. The server validates each selected book and its available stock.

- 4. The server determines the applicable unit price: member_price for an authenticated member; otherwise price.

- 5. The server calculates the total from database values, creates the orders record and order_items rows, and reduces stock within a database transaction.

- 6. The system returns an order confirmation only after the database transaction succeeds.

Alternative / exception flows: empty cart blocks checkout; invalid guest fields require correction; quantity above available stock is rejected; a database error rolls back the transaction and must not display a success message.

## 4.2. UC-06 Customer Login

Target precondition: the account exists in users. The user submits email and password; the server retrieves the account, verifies the password with password_verify() against password_hash, creates an authenticated PHP session, and reads role/is_member. Invalid credentials return a general error. Current prototype status: role selection uses the user records loaded for demonstration; it does not verify a password or establish a customer/admin session.

## 4.3. UC-09 / UC-10 Manage Books and Categories

Target flow: an authenticated administrator opens the relevant management page, chooses add/edit/delete, enters data, and submits it. The server validates required fields, category references, price/stock values, and unique category slugs before committing the change to MySQL. Current prototype status: the management screens and validation are client-side; changes are stored in localStorage rather than the database.

## 4.4. UC-11 Manage Orders

Target flow: an authenticated administrator views an order and its line items, selects a new order status, and saves the change. The server checks administrator authorization and persists the status. Current prototype status: order details and status controls are demonstrated in the browser; a database update endpoint is not part of the supplied package.

## 4.5. UC-12 Record UI Events Asynchronously

Trigger: page load, route change, selected action click, catalog-sort change, supported form submission, or browser reconnection. The client queues a compact event record in localStorage, then sends a POST request containing JSON and an X-CSRF-Token header to async_api.php. The endpoint validates the request method, token, allowed event, route, payload size, and per-session request rate. It inserts event metadata into async_events and returns JSON containing ok, stored, event_id, and server_time. On successful response, the client removes the event from its queue and shows a status badge; on temporary failure, it retains and retries the queued event.

Postcondition: the event metadata is recorded in async_events. This telemetry operation is separate from business checkout and does not persist orders, stock, book CRUD, or user authentication.


## V. CUSTOMER AND ADMINISTRATOR USER FLOWS

## 5.1. Customer Purchase Flow

The customer-facing flow begins with catalog discovery and continues through product details, cart review, checkout, price display, and the order confirmation screen. Guest price and member-price differences are demonstrated by selecting a sample role. In the current app.js snapshot, cart, checkout, order preview, and stock changes remain browser-side.

*Figure 2. Current customer purchase prototype fiow and its persistence boundary.*

## 5.2. Administrator Flow

The administrator prototype uses the role selector to enter the administrator experience, opens the dashboard, and selects book, category, or order management. These screens demonstrate intended interactions, but server-side authorization and database-persistent changes remain pending.

## MOC BOOKS ADMINISTRATOR PROTOTYPE FLOW

*Figure 3. Administrator prototype fiow and pending server-side persistence.*


## VI. DATA FLOW DIAGRAM (DFD)

## 6.1. Level 0 Context Diagram

The context diagram treats the Online Clean Book application as one process and shows the information exchanged with its three external actor groups. The diagram represents the overall system boundary, not a claim that every business flow is already persisted in MySQL.

*Figure 4. DFD Level 0 system context and external actors.*

## 6.2. Level 1 Main Processes and Data Stores

The Level 1 DFD decomposes the application into catalog browsing, cart/checkout, account/member selection, administrator management, and asynchronous event logging. Solid paths indicate current data reads or the verified async event-write path. Dashed red paths indicate planned server-side persistence that is not present in the supplied prototype snapshot.


## MOC BOOKS — DFD LEVEL 1 (CURRENT AND TARGET DATA FLOWS)

nial order data

*Figure 5. DFD Level 1 implemented and pending data fiows.*


## VII. DATABASE DESIGN AND ERD

Database name: bookstore_dev. DBMS: MySQL. Character set: utf8mb4. The Herd-ready schema contains five business tables plus one supporting asynchronous-event table.

## MOC BOOKS — UPDATED ENTITY RELATIONSHIP DIAGRAM

*Figure 6. Updated Mộc Books ERD with the independent async_events table.*

*7.2. Table Descriptions*

| Table | Main Columns and Keys | Purpose |
| --- | --- | --- |
| categories | id INT PK; name VARCHAR(100); slug VARCHAR(120) UNIQUE; description TEXT | Book categories. |
| books | id INT PK; category_id INT FK; title; author; price DECIMAL(10,2); member_price DECIMAL(10,2); stock_quantity INT; image_url; description | Book details, price rules, category, and stock. |
| users | id INT PK; name; email UNIQUE; password_hash; role ENUM(customer, admin); is_member BOOLEAN | Customer/admin demo records and membership flag. |
| orders | id INT PK; user_id INT NULL FK; guest_name; guest_email; guest_phone; guest_address; total_amount; status; created_at | Order header and guest contact details. |
| order_items | id INT PK; order_id INT FK; book_id INT FK; quantity INT; unit_price DECIMAL(10,2) | Books, quantities, and unit price per order line. |


| async_events | id BIGINT UNSIGNED PK; event_name; route; cart_lines; cart_quantity; book_count; created_at | Asynchronous interface-event telemetry; no business-table foreign keys. |
| --- | --- | --- |


## 7.3. Database Scripts and Sample Data

- schema.sql creates bookstore_dev, the five business tables, and async_events.

- seed.sql inserts the sample categories, books, users, and example orders/order_items. The demo hashes correspond to the local passwords documented in README_HERD.md.

- add_async_events.sql adds only the async_events table when an older five-table database already exists; it does not recreate the business tables.

- The DATA / SAMPLE_DATA folder in the GitHub repository remains the reference location for the team-managed sample data and related resources.

[GitHub repository: https://github.com/HanLapLHDC/Online-Clean-Book](https://github.com/HanLapLHDC/Online-Clean-Book)

[Sample data: https://github.com/HanLapLHDC/Online-Clean-Book/tree/main/OnlineCleanBook/DATA%20/ SAMPLE_DATA](https://github.com/HanLapLHDC/Online-Clean-Book/tree/main/OnlineCleanBook/DATA/SAMPLE_DATA)

## VIII. RELATIONSHIPS, BUSINESS RULES, AND DATA VALIDATION

## 8.1. Relationships

- categories (1) (N) books: each book references one category through books.category_id.

- users (1) (N) orders: one registered user may place many orders; guest orders use orders.user_id = NULL.

- orders (1) (N) order_items: an order contains one or more order lines.

- books (1) (N) order_items: one book may appear in many order lines over time.

- async_events is a standalone event-log table and has no relationship to the business entities.

## 8.2. Business Rules

- Use member_price only for a customer whose users.is_member value is TRUE; otherwise use price. In the current prototype, this is a client-side display rule based on the selected demo user.

- For persisted orders, order_items.unit_price must retain the price applied at purchase time so later catalog- price changes do not rewrite historical order values.

- For a guest order, guest_name, guest_email, guest_phone, and guest_address are stored in orders while user_id is NULL.

- The initial orders.status column is VARCHAR(50) with default value pending; the schema does not enforce a fixed status vocabulary.

- The current project scope does not include a payment table, shipping table, or automatic bank- transfer verification.

## 8.3. Data Integrity and Validation

- Book title, author, category_id, price, and member_price are required; price values must be non-negative and stock_quantity must not be negative.

- Cart quantities must be positive integers and must not exceed available stock.

- For the target persisted checkout, the server must calculate unit prices and totals from database records rather than trusting values sent by the browser.

- Order creation and stock reduction should run in one database transaction; failures must roll back all changes.

- Passwords must be stored as password hashes and verified server-side; plaintext passwords must not be stored.

- Only authenticated users whose server-side role is admin may execute administrative operations.

- The async endpoint accepts POST only, checks a session CSRF token, limits request payloads to 8 KB, validates allowed event names/routes, and applies a per-session event-rate limit. These controls apply to telemetry, not to business checkout.


## IX. PROTOTYPE AND IMPLEMENTATION OVERVIEW

## 9.1. Runtime Architecture

The browser loads index.php through Laravel Herd at the local site mocbooks.test. index.php includes config.php, opens the shared PDO MySQL connection, retrieves the initial data set, and exposes that data to app.js. app.js renders the storefront and manages the current prototype state. async_bridge.js independently tracks selected UI events and calls async_api.php, which writes to async_events. The team used a local MySQL service and phpMyAdmin for database inspection.

*Figure 7. Runtime components and asynchronous event path.*

## 9.2. Source Package Inventory

| File | Responsibility |
| --- | --- |
| index.php | PHP entry point; connects through config.php and retrieves initial database records. |
| config.php | Shared PDO configuration, PHP session setup, and CSRF token helper. |
| app.js | Storefront rendering, catalog filtering, demo role selection, cart, checkout and admin browser interactions. |
| styles.css | Visual styling and responsive layout. |
| async_bridge.js | Asynchronous UI-event queue, Fetch API client, retry handling, and status badge. |
| async_api.php | POST endpoint that validates telemetry payloads and inserts event rows. |
| schema.sql | Creates the database, five business tables, and async_events. |
| seed.sql | Initial sample categories, books, users, orders, and order items. |
| add_async_events.sql | Migration for adding the event-log table to an existing five-table database. |

## 9.3. Current Implementation Boundaries

- Implemented database read: index.php uses PDO to read categories, books, users (without password_hash), orders, and order_items.

- Prototype-only browser state: app.js stores the current catalog snapshot, selected demo user, cart, order records, order items, stock changes, and administrator edits in localStorage.

- Implemented database write: async_api.php writes asynchronous UI-event metadata to async_events after request validation.

- Not implemented in this supplied package: server-side login/authentication, persisted business checkout, stock transaction, protected administrator CRUD, and persisted order-status updates.


## X. ASYNCHRONOUS INTERACTION IMPLEMENTATION

The project includes one concrete asynchronous interaction, implemented through async_bridge.js and async_api.php. The feature sends UI-event metadata without a full-page form submission and records the event in MySQL. It is a telemetry feature and does not replace the business APIs that are still pending.

*Figure 8. Asynchronous event fiow: browser queue → Fetch API → PHP endpoint → MySQL.*

## 10.1. Client-Side Behavior

- The bridge tracks page load, hash-route changes, selected data-action clicks, catalog-sort changes, supported form submissions, and online reconnection.

- Each event contains an event name, current route, and simple metrics (cart line count, total cart quantity, and catalog book count).

- Events are placed in a bounded localStorage queue (maximum 50 entries) and sent as JSON using Fetch API with same-origin credentials and the X-CSRF-Token header.

- When a server response confirms ok=true and stored=true, the event is removed from the queue and the page displays the generated event ID. If the request fails, queued events remain available for retry.

## 10.2. Server-Side Validation and Persistence

- async_api.php accepts POST requests only and returns JSON responses.

- It validates the session CSRF token, request size, JSON structure, allowed event names, allowed route format, and a per-session rate limit.

- It inserts event_name, route, cart_lines, cart_quantity, and book_count into async_events, where id and created_at are generated by MySQL.

- A successful insert returns HTTP 200 with ok=true, stored=true, event_id, and server_time. A database problem returns HTTP 503 with a safe error message.

## 10.3. Local Verification Evidence

The team tested the asynchronous endpoint on the local Herd site at mocbooks.test. The browser Network panel shows a POST Fetch request to async_api.php with HTTP 200. The phpMyAdmin structure view shows the async_events table and 22 rows at the time of the screenshot. Together, these screenshots provide evidence of the endpoint response and event rows in the database.

*Figure 9. Browser Network evidence: async_api.php returned HTTP 200. Figure 10. phpMyAdmin*

*evidence: async_events table displayed 22 rows.*

Verification boundary The screenshots establish an HTTP 200 response and show 22 rows in async_events. The report does not infer that all 22 rows came from a single request; individual event IDs/timestamps can be inspected directly in phpMyAdmin.


## XI. TESTING AND VERIFICATION

Testing status below is based on the supplied Herd-ready source package and the local screenshots shared during this session. PASS is used only where visible evidence supports the result. Browser-only prototype actions are marked PARTIAL / PENDING rather than reported as completed database transactions.

| Test ID | Test Area | Verification Activity | Expected Result Observed Result / | Status |
| --- | --- | --- | --- | --- |
| TV-01 | PHP/MySQL initial data loading | Open mocbooks.test and load the storefront. Inspect database tables and seeded records. | The PHP page loads and displays data retrieved by index.php from bookstore_dev. | PASS storefront opened on Herd; initial database read path is present. |
| TV-02 | Guest cart and checkout | Add a book, change quantity, enter guest details, and submit checkout. | Cart validation and checkout flow operate; final order and order_items are persisted in MySQL. | PARTIAL prototype flow exists, but order state is stored in localStorage, not MySQL. |
| TV-03 | Customer login and member price | Log in with a customer/member account and compare price calculation. | A valid session is created and the server applies the correct member/regular price. | PARTIAL demo role selection and client-side price display exist; real login/session and server-side price enforcement remain pending. |
| TV-04 | Administrator operations | Open admin screens; add/edit/delete a book/category; inspect and update an order. | Authorized admin changes are saved to MySQL and visible after reload. | PARTIAL screens and browser-side updates exist; server authorization and persistent CRUD/status updates remain pending. |
| TV-05 | Asynchronous UI event logging | Refresh the page; inspect Network Fetch/XHR; inspect async_events in phpMyAdmin. | async_api.php returns HTTP 200 and the event is stored in async_events. | PASS screenshot shows HTTP 200 for async_api.php; phpMyAdmin shows async_events with 22 rows. |
| TV-06 | Responsive interface | Inspect the storefront at desktop and narrow viewport widths. | Layout adapts without horizontal overflow or inaccessible controls. | CSS includes responsive rules; a complete multi- device execution record was not supplied. |

## 11.1. How to Repeat the Async Test [URL 🔗](http://mocbooks.test/)

- 1. Open http://mocbooks.test and open Developer Tools → Network → Fetch/XHR. [URL 🔗](http://mocbooks.test/)

- 2. Refresh the page or use a navigation/action control that is tracked by async_bridge.js.

- 3. Select async_api.php and verify Status 200. The JSON body should contain ok=true, stored=true, and an event_id.


- 4. In phpMyAdmin, run: SELECT id, event_name, route, cart_lines, cart_quantity, book_count, created_at FROM async_events ORDER BY id DESC LIMIT 20;


## XII. PROGRESS NOTE

## 12.1. Work Completed

- Prepared the bookstore_dev MySQL schema and seed data for categories, books, users, orders, and order_items.

- Built the PHP entry point and initial PDO/MySQL data-loading path for the storefront.

- Prepared the Mộc Books customer-facing prototype, responsive stylesheet, cart/checkout demonstration, member-price display, and administrator screens.

- Added shared PHP database/session configuration and the asynchronous event endpoint.

- Added async_bridge.js, async_events schema/migration, and local Herd setup documentation.

- Verified the asynchronous request on Herd with HTTP 200 and observed 22 rows in async_events.

- Prepared updated actors, functional requirements, use cases, customer/admin flows, DFD, six-table ERD, business rules, and verification records.

## 12.2. Current Development Focus

The current technical milestone demonstrates initial catalog loading and asynchronous UI-event logging. The main unfinished work is to move customer and administrator business operations from browser localStorage to validated PHP/MySQL endpoints. This includes secure login, member-price authorization, guest/customer order persistence, stock transactions, protected catalog CRUD, and order-status persistence.

## 12.3. Next Development Tasks

- 1. Implement PHP session authentication and server-side role authorization for Customer and Administrator.

- 2. Implement checkout endpoints that validate stock, calculate trusted prices, create orders/order_items, and update stock transactionally.

- 3. Implement persistent book/category CRUD and order-status updates for administrators.

- 4. Retest TV-02, TV-03, and TV-04 against MySQL and update their observed statuses with evidence.

- 5. Review the latest GitHub DATA/SAMPLE_DATA resources and source code to ensure they match the report before final submission.
