SET SERVEROUTPUT ON;

CREATE OR REPLACE PROCEDURE register_user (
    p_user_id       IN NUMBER,
    p_name          IN VARCHAR2,
    p_email         IN VARCHAR2,
    p_password_hash IN VARCHAR2,
    p_phone         IN VARCHAR2,
    p_role          IN VARCHAR2,
    p_vehicle_type  IN VARCHAR2 DEFAULT NULL
) AS
    v_agent_id NUMBER;
BEGIN
    INSERT INTO users (user_id, name, email, password_hash, phone, role, created_at)
    VALUES (p_user_id, p_name, p_email, p_password_hash, p_phone, p_role, SYSDATE);
    
    IF LOWER(p_role) = 'agent' THEN
        INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating)
        VALUES (p_user_id, p_user_id, 'available', NVL(p_vehicle_type, 'Bicycle'), 5.0);
    END IF;
    
    COMMIT;
    DBMS_OUTPUT.PUT_LINE('User ' || p_name || ' registered successfully.');
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE_APPLICATION_ERROR(-20001, 'Error registering user: ' || SQLERRM);
END;
/

CREATE OR REPLACE PROCEDURE add_to_cart (
    p_customer_id IN NUMBER,
    p_item_id     IN NUMBER,
    p_quantity    IN NUMBER
) AS
    v_cart_id NUMBER;
    v_item_count NUMBER;
    v_new_cart_item_id NUMBER;
BEGIN
    BEGIN
        SELECT cart_id INTO v_cart_id FROM carts WHERE customer_id = p_customer_id;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            SELECT NVL(MAX(cart_id), 0) + 1 INTO v_cart_id FROM carts;
            INSERT INTO carts (cart_id, customer_id, created_at, updated_at)
            VALUES (v_cart_id, p_customer_id, SYSDATE, SYSDATE);
    END;
    
    SELECT COUNT(*) INTO v_item_count 
    FROM cart_items 
    WHERE cart_id = v_cart_id AND item_id = p_item_id;
    
    IF v_item_count > 0 THEN
        UPDATE cart_items 
        SET quantity = quantity + p_quantity 
        WHERE cart_id = v_cart_id AND item_id = p_item_id;
    ELSE
        SELECT NVL(MAX(cart_item_id), 0) + 1 INTO v_new_cart_item_id FROM cart_items;
        INSERT INTO cart_items (cart_item_id, cart_id, item_id, quantity)
        VALUES (v_new_cart_item_id, v_cart_id, p_item_id, p_quantity);
    END IF;
    
    UPDATE carts SET updated_at = SYSDATE WHERE cart_id = v_cart_id;
    
    COMMIT;
    DBMS_OUTPUT.PUT_LINE('Item added to cart.');
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE_APPLICATION_ERROR(-20002, 'Error adding to cart: ' || SQLERRM);
END;
/

CREATE OR REPLACE PROCEDURE place_order_from_cart (
    p_customer_id      IN NUMBER,
    p_restaurant_id    IN NUMBER,
    p_delivery_address IN CLOB,
    p_payment_method   IN VARCHAR2,
    p_delivery_fee     IN NUMBER,
    p_out_order_id     OUT NUMBER
) AS
    v_cart_id NUMBER;
    v_total_amount NUMBER := 0;
    v_order_id NUMBER;
    v_order_item_id NUMBER;
    v_cart_empty_check NUMBER;
BEGIN
    SELECT cart_id INTO v_cart_id FROM carts WHERE customer_id = p_customer_id;
    
    SELECT COUNT(*) INTO v_cart_empty_check FROM cart_items WHERE cart_id = v_cart_id;
    IF v_cart_empty_check = 0 THEN
        RAISE_APPLICATION_ERROR(-20003, 'Cart is empty. Cannot place order.');
    END IF;
    
    SELECT SUM(ci.quantity * mi.price) INTO v_total_amount
    FROM cart_items ci
    JOIN menu_items mi ON ci.item_id = mi.item_id
    WHERE ci.cart_id = v_cart_id;
    
    v_total_amount := v_total_amount + p_delivery_fee;
    
    SELECT NVL(MAX(order_id), 1000) + 1 INTO v_order_id FROM orders;
    p_out_order_id := v_order_id;
    
    INSERT INTO orders (order_id, customer_id, restaurant_id, agent_id, status, total_amount, delivery_address, created_at, updated_at)
    VALUES (v_order_id, p_customer_id, p_restaurant_id, NULL, 'placed', v_total_amount, p_delivery_address, SYSDATE, SYSDATE);
    
    FOR item IN (
        SELECT ci.item_id, ci.quantity, mi.name, mi.price 
        FROM cart_items ci
        JOIN menu_items mi ON ci.item_id = mi.item_id
        WHERE ci.cart_id = v_cart_id
    ) LOOP
        SELECT NVL(MAX(order_item_id), 0) + 1 INTO v_order_item_id FROM order_items;
        
        INSERT INTO order_items (order_item_id, order_id, item_id, quantity, unit_price)
        VALUES (v_order_item_id, v_order_id, item.item_id, item.quantity, item.price);
    END LOOP;
    
    INSERT INTO order_status_history (history_id, order_id, status, changed_at, changed_by)
    VALUES (NVL((SELECT MAX(history_id) FROM order_status_history), 0) + 1, v_order_id, 'placed', SYSDATE, p_customer_id);
    
    DELETE FROM cart_items WHERE cart_id = v_cart_id;
    
    COMMIT;
    DBMS_OUTPUT.PUT_LINE('Order #' || v_order_id || ' placed successfully.');
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE_APPLICATION_ERROR(-20004, 'Error placing order: ' || SQLERRM);
END;
/

CREATE OR REPLACE PROCEDURE update_order_status (
    p_order_id   IN NUMBER,
    p_status     IN VARCHAR2,
    p_changed_by IN NUMBER
) AS
    v_history_id NUMBER;
    v_agent_id NUMBER;
BEGIN
    UPDATE orders 
    SET status = LOWER(p_status), updated_at = SYSDATE 
    WHERE order_id = p_order_id;
    
    SELECT agent_id INTO v_agent_id FROM orders WHERE order_id = p_order_id;
    
    IF LOWER(p_status) = 'delivered' THEN
        IF v_agent_id IS NOT NULL THEN
            UPDATE delivery_agents SET status = 'available' WHERE agent_id = v_agent_id;
        END IF;
    END IF;
    
    SELECT NVL(MAX(history_id), 0) + 1 INTO v_history_id FROM order_status_history;
    INSERT INTO order_status_history (history_id, order_id, status, changed_at, changed_by)
    VALUES (v_history_id, p_order_id, LOWER(p_status), SYSDATE, p_changed_by);
    
    COMMIT;
    DBMS_OUTPUT.PUT_LINE('Order #' || p_order_id || ' status updated to ' || p_status);
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE_APPLICATION_ERROR(-20005, 'Error updating order status: ' || SQLERRM);
END;
/

CREATE OR REPLACE PROCEDURE submit_review (
    p_customer_id   IN NUMBER,
    p_restaurant_id IN NUMBER,
    p_order_id      IN NUMBER,
    p_rating        IN NUMBER,
    p_comment       IN CLOB
) AS
    v_review_id NUMBER;
    v_avg_rating NUMBER(2,1);
BEGIN
    SELECT NVL(MAX(review_id), 0) + 1 INTO v_review_id FROM reviews;
    INSERT INTO reviews (review_id, customer_id, restaurant_id, order_id, rating, comments, created_at)
    VALUES (v_review_id, p_customer_id, p_restaurant_id, p_order_id, p_rating, p_comment, SYSDATE);
    
    SELECT ROUND(AVG(rating), 1) INTO v_avg_rating 
    FROM reviews 
    WHERE restaurant_id = p_restaurant_id;
    
    UPDATE restaurants 
    SET rating = v_avg_rating 
    WHERE restaurant_id = p_restaurant_id;
    
    COMMIT;
    DBMS_OUTPUT.PUT_LINE('Review submitted. Restaurant average rating updated to ' || v_avg_rating);
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE_APPLICATION_ERROR(-20006, 'Error submitting review: ' || SQLERRM);
END;
/




