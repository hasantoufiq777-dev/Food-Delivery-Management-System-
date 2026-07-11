# FlameRoute — Premium Food Delivery Management System
## Presentation Slides Outline for Canva Docs to Decks

---

# Slide 1: FlameRoute
### Streamlined Food Delivery Logistics & Dashboard
* A unified platform bridging Kitchens, Couriers, Customers, and Administrators.
* Fully database-backed system with modular portal interfaces.
* Built using modern web aesthetics and Oracle SQL database schemas.

---

# Slide 2: Project Overview & Objectives
### Solving the Last-Mile Delivery Challenge
* **Real-time Synchronization:** Ensures instant updates between customer order placement, kitchen prep, and courier dispatch.
* **Role Separation:** 4 customized portal dashboards targeting distinct user needs.
* **Modern Interface:** High-fidelity aesthetics replacing outdated legacy dashboards.

---

# Slide 3: Modular Portal Architecture
### Four Interfaces, One Unified Database
* **👤 Customer Portal:** Browse restaurants, build active carts, place orders, and review delivery.
* **🍽️ Shop (Restaurant) Portal:** Dynamic menu management, item availability, and order prep status.
* **🛵 Courier (Agent) Portal:** Dispatch queue, active routing, and delivery state tracking.
* **🛡️ Admin Portal:** System analytics, simulated user switching, and diagnostics.

---

# Slide 4: Database Design Philosophy
### Robust Relational Foundation in Oracle SQL
* **Relational Integrity:** Implements rigorous relational modeling in Third Normal Form (3NF).
* **Robust Datatypes:** Standardized Oracle type mapping (NUMBER, VARCHAR2, CLOB, DATE).
* **System Security:** Isolated hashes for passwords and role constraints to ensure authorization.

---

# Slide 5: DB Schema: User Identity & Directory
### Core Table: `users`
* **Structure:** `user_id` (PK), `name`, `email` (Unique), `password_hash`, `phone`, `role`, `created_at`.
* **Roles Mapping:** Identifies if a user is a `customer`, `restaurant`, `agent`, or `admin`.
* **Indexation:** Fast email-based lookup for authentication routines.

---

# Slide 6: DB Schema: Restaurant Profile Management
### Core Table: `restaurants`
* **Structure:** `restaurant_id` (PK), `user_id` (FK), `name`, `address` (CLOB), `cuisine_type`, `rating`, `status`, `image_url`.
* **User Linking:** Extends the `users` table via foreign key constraints (`fk_rest_user`).
* **Rating System:** Dynamic decimal field (`NUMBER(2,1)`) with a default value of `0.0`.

---

# Slide 7: DB Schema: Menu Items & Categories
### Core Table: `menu_items`
* **Structure:** `item_id` (PK), `restaurant_id` (FK), `name`, `description` (CLOB), `price`, `category`, `image_url`, `is_available`.
* **State Control:** The boolean numeric `is_available` flag controls visibility inside customer browses.
* **Cascading Safety:** Menu items automatically delete if the parent restaurant is removed.

---

# Slide 8: DB Schema: Delivery Agents (Couriers)
### Core Table: `delivery_agents`
* **Structure:** `agent_id` (PK), `user_id` (FK), `status`, `vehicle_type`, `rating`.
* **Status Enum:** Toggles courier state between `available`, `busy`, or `offline`.
* **Vehicle Typing:** Custom profiling for vehicle logistics (e.g., bicycle, motorcycle, car).

---

# Slide 9: DB Schema: Active Shopping Carts
### Tables: `carts` & `cart_items`
* **Cart Table:** `cart_id` (PK), `customer_id` (FK), timestamps for creation and updates.
* **Cart Items:** `cart_item_id` (PK), `cart_id` (FK), `item_id` (FK), `quantity`.
* **Transient State:** Stores order items before purchase checkout is finalized.

---

# Slide 10: DB Schema: Order Transactions
### Core Table: `orders`
* **Structure:** `order_id` (PK), `customer_id` (FK), `restaurant_id` (FK), `agent_id` (FK), `status`, `total_amount`, `delivery_address` (CLOB).
* **Order Status States:** Defaults to `placed`, and updates to `preparing`, `ready for pickup`, `picked up`, and `delivered`.
* **Nullable Assignee:** `agent_id` is nullable by default until a courier accepts the dispatch.

---

# Slide 11: DB Schema: Order Line Items
### Core Table: `order_items`
* **Structure:** `order_item_id` (PK), `order_id` (FK), `item_id` (FK), `quantity`, `unit_price`.
* **Historical Accuracy:** Captures the exact price of the item at the time of purchase (`unit_price`) to insulate records from future menu price updates.

---

# Slide 12: DB Schema: Order Lifecycle History
### Core Table: `order_status_history`
* **Structure:** `history_id` (PK), `order_id` (FK), `status`, `changed_at`, `changed_by` (FK).
* **Audit Trail:** Tracks exact historical transitions of order states for dispatch analytics.
* **User Context:** Records which entity (Customer, Shop, Admin, or Courier) triggered the state update.

---

# Slide 13: DB Schema: Customer Feedback & Reviews
### Core Table: `reviews`
* **Structure:** `review_id` (PK), `customer_id` (FK), `restaurant_id` (FK), `order_id` (FK), `rating`, `comments` (CLOB), `created_at`.
* **Relational Loop:** Links the reviewer, the target vendor, and the exact order index to ensure rating validity.

---

# Slide 14: Database Procedural Logic (PL/SQL)
### Encapsulating Actions with Stored Procedures
* **User Registration:** Custom procedure `register_user` to register and validate customer records.
* **Transactional Safety:** Uses database-level execution blocks to run multi-table inserts.
* **Consistency:** Insulates front-end logic from schema alterations.

---

# Slide 15: Referential Integrity & Cascades
### Strict Constraints for Data Integrity
* **Parent-Child Safety:** Foreign key constraints restrict orphaned cart items or order rows.
* **Deletions:** Implements `ON DELETE CASCADE` on carts and items, and `ON DELETE SET NULL` on courier mappings.
* **Constraints:** Enforces rating boundaries (0 to 5) and unique email constraints.

---

# Slide 16: System Aesthetics & Front-end Integration
### Bringing the Database to Life
* **PHP PDO Layer:** Connects the Oracle database securely using PDO parameters.
* **Visual System:** Custom Dark/Light palette options built using vanilla CSS variables.
* **Interactive Testing:** Pre-populated simulation credentials for all 4 roles to allow instant feature validation.
