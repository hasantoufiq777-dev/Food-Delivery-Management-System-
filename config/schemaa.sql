
CREATE TABLE users (
    user_id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL,
    email VARCHAR2(150) UNIQUE NOT NULL,
    password_hash VARCHAR2(255) NOT NULL,
    phone VARCHAR2(20),
    role VARCHAR2(20) NOT NULL, -- 'admin', 'customer', 'restaurant', 'agent'
    created_at DATE DEFAULT SYSDATE
);


CREATE TABLE restaurants (
    restaurant_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    name VARCHAR2(150) NOT NULL,
    address CLOB,
    cuisine_type VARCHAR2(50) NOT NULL,
    rating NUMBER(2,1) DEFAULT 0.0,
    status VARCHAR2(20) DEFAULT 'active',
    image_url VARCHAR2(255),
    CONSTRAINT fk_rest_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE menu_items (
    item_id NUMBER PRIMARY KEY,
    restaurant_id NUMBER NOT NULL,
    name VARCHAR2(150) NOT NULL,
    description CLOB,
    price NUMBER(10,2) NOT NULL,
    category VARCHAR2(50) NOT NULL,
    image_url VARCHAR2(255),
    is_available NUMBER(1) DEFAULT 1 NOT NULL,
    CONSTRAINT fk_menu_rest FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
);


CREATE TABLE delivery_agents (
    agent_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    status VARCHAR2(20) DEFAULT 'available',
    vehicle_type VARCHAR2(50) NOT NULL,
    rating NUMBER(2,1) DEFAULT 0.0,
    CONSTRAINT fk_agent_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE carts (
    cart_id NUMBER PRIMARY KEY,
    customer_id NUMBER NOT NULL, -- references users(user_id)
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_cart_customer FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE cart_items (
    cart_item_id NUMBER PRIMARY KEY,
    cart_id NUMBER NOT NULL,
    item_id NUMBER NOT NULL,
    quantity NUMBER NOT NULL,
    CONSTRAINT fk_item_cart FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
    CONSTRAINT fk_item_menu FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE
);


CREATE TABLE orders (
    order_id NUMBER PRIMARY KEY,
    customer_id NUMBER NOT NULL, -- references users(user_id)
    restaurant_id NUMBER NOT NULL, -- references restaurants(restaurant_id)
    agent_id NUMBER DEFAULT NULL, -- references delivery_agents(agent_id)
    status VARCHAR2(20) DEFAULT 'placed' NOT NULL,
    total_amount NUMBER(10,2) NOT NULL,
    delivery_address CLOB NOT NULL,
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_agent FOREIGN KEY (agent_id) REFERENCES delivery_agents(agent_id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    order_item_id NUMBER PRIMARY KEY,
    order_id NUMBER NOT NULL,
    item_id NUMBER NOT NULL, -- references menu_items(item_id)
    quantity NUMBER NOT NULL,
    unit_price NUMBER(10,2) NOT NULL,
    CONSTRAINT fk_order_item_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_item_menu FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE
);

CREATE TABLE order_status_history (
    history_id NUMBER PRIMARY KEY,
    order_id NUMBER NOT NULL,
    status VARCHAR2(20) NOT NULL,
    changed_at DATE DEFAULT SYSDATE,
    changed_by NUMBER NOT NULL, -- references users(user_id)
    CONSTRAINT fk_history_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE reviews (
    review_id NUMBER PRIMARY KEY,
    customer_id NUMBER NOT NULL, -- references users(user_id)
    restaurant_id NUMBER NOT NULL, -- references restaurants(restaurant_id)
    order_id NUMBER NOT NULL, -- references orders(order_id)
    rating NUMBER NOT NULL,
    comments CLOB,
    created_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_review_customer FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_review_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE,
    CONSTRAINT fk_review_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);