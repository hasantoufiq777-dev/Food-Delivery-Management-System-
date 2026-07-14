
-- 1. Auto-create Courier Profile on User Registration,,Auto-create Courier Profile
CREATE OR REPLACE TRIGGER trg_create_delivery_agent
AFTER INSERT ON users
FOR EACH ROW
WHEN (LOWER(NEW.role) = 'agent')
BEGIN
    INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating)
    VALUES (:NEW.user_id, :NEW.user_id, 'available', 'Bicycle', 5.0);
END;
/

-- 2. Auto-log Order Status transitions,,Order Status Timeline Audito
CREATE OR REPLACE TRIGGER trg_log_order_status_history
AFTER INSERT OR UPDATE OF status ON orders
FOR EACH ROW
DECLARE
    v_history_id NUMBER;
BEGIN
    SELECT NVL(MAX(history_id), 0) + 1 INTO v_history_id FROM order_status_history;
    
    INSERT INTO order_status_history (history_id, order_id, status, changed_at, changed_by)
    VALUES (v_history_id, :NEW.order_id, :NEW.status, SYSDATE, :NEW.customer_id);
END;
/

-- 3. Auto-reset Courier availability status on Delivery/Cancellation (Auto-Release Courier)
CREATE OR REPLACE TRIGGER trg_reset_agent_status
AFTER UPDATE OF status ON orders
FOR EACH ROW
WHEN (LOWER(NEW.status) IN ('delivered', 'cancelled') AND NEW.agent_id IS NOT NULL)
BEGIN
    UPDATE delivery_agents 
    SET status = 'available' 
    WHERE agent_id = :NEW.agent_id;
END;
/


