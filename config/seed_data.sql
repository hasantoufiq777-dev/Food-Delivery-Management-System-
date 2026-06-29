-- ========================================================
-- 1. Seed USERS (Unified User Table)
-- ========================================================
-- Admin
INSERT INTO users (user_id, name, email, password_hash, phone, role) 
VALUES (1, 'System Administrator', 'admin@example.com', 'admin123', '+1-555-0000', 'admin');

-- Restaurants (User IDs: 10 - 14)
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (10, 'The Spice Garden', 'spicegarden@example.com', 'password', '+1-555-0101', 'restaurant');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (11, 'Sakura Sushi Bar', 'sakura@example.com', 'password', '+1-555-0102', 'restaurant');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (12, 'Bella Napoli', 'bellanapoli@example.com', 'password', '+1-555-0103', 'restaurant');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (13, 'Dragon Wok', 'dragonwok@example.com', 'password', '+1-555-0104', 'restaurant');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (14, 'Le Petit Bistro', 'lepetitbistro@example.com', 'password', '+1-555-0105', 'restaurant');

-- Customers (User IDs: 20 - 24)
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (20, 'Alice Johnson', 'alice@example.com', 'password', '+1-555-1001', 'customer');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (21, 'Bob Martinez', 'bob@example.com', 'password', '+1-555-1002', 'customer');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (22, 'Carol Chen', 'carol@example.com', 'password', '+1-555-1003', 'customer');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (23, 'David Kim', 'david@example.com', 'password', '+1-555-1004', 'customer');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (24, 'Eva Rodriguez', 'eva@example.com', 'password', '+1-555-1005', 'customer');

-- Agents (User IDs: 30 - 34)
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (30, 'Marcus Reed', 'marcus@example.com', 'password', '+1-555-2001', 'agent');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (31, 'Priya Sharma', 'priya@example.com', 'password', '+1-555-2002', 'agent');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (32, 'Tomás Herrera', 'tomas@example.com', 'password', '+1-555-2003', 'agent');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (33, 'Aisha Johnson', 'aisha@example.com', 'password', '+1-555-2004', 'agent');
INSERT INTO users (user_id, name, email, password_hash, phone, role) VALUES (34, 'Liam O''Brien', 'liam@example.com', 'password', '+1-555-2005', 'agent');

-- ========================================================
-- 2. Seed RESTAURANTS Table
-- ========================================================
INSERT INTO restaurants (restaurant_id, user_id, name, address, cuisine_type, rating, status, image_url) 
VALUES (1, 10, 'The Spice Garden', '42 Curry Lane, Downtown', 'Indian', 4.7, 'active', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop');
INSERT INTO restaurants (restaurant_id, user_id, name, address, cuisine_type, rating, status, image_url) 
VALUES (2, 11, 'Sakura Sushi Bar', '88 Blossom Avenue, Midtown', 'Japanese', 4.9, 'active', 'https://images.unsplash.com/photo-1579027989536-b7b1f875659b?w=400&h=300&fit=crop');
INSERT INTO restaurants (restaurant_id, user_id, name, address, cuisine_type, rating, status, image_url) 
VALUES (3, 12, 'Bella Napoli', '15 Pasta Street, Little Italy', 'Italian', 4.5, 'active', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=400&h=300&fit=crop');
INSERT INTO restaurants (restaurant_id, user_id, name, address, cuisine_type, rating, status, image_url) 
VALUES (4, 13, 'Dragon Wok', '200 Noodle Road, Chinatown', 'Chinese', 4.3, 'active', 'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=400&h=300&fit=crop');
INSERT INTO restaurants (restaurant_id, user_id, name, address, cuisine_type, rating, status, image_url) 
VALUES (5, 14, 'Le Petit Bistro', '7 Rue de la Paix, Uptown', 'French', 4.8, 'active', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&h=300&fit=crop');

-- ========================================================
-- 3. Seed DELIVERY_AGENTS Table
-- ========================================================
INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating) VALUES (1, 30, 'available', 'Motorcycle', 4.8);
INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating) VALUES (2, 31, 'busy', 'Bicycle', 4.6);
INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating) VALUES (3, 32, 'available', 'Car', 4.9);
INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating) VALUES (4, 33, 'busy', 'Motorcycle', 4.7);
INSERT INTO delivery_agents (agent_id, user_id, status, vehicle_type, rating) VALUES (5, 34, 'available', 'Bicycle', 4.5);

-- ========================================================
-- 4. Seed MENU_ITEMS Table
-- ========================================================
-- The Spice Garden (1)
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (1, 1, 'Butter Chicken', 'Creamy tomato-based curry with tender chicken pieces', 14.99, 'Main Course', 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?w=300&h=200&fit=crop', 1);
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (2, 1, 'Garlic Naan', 'Fresh-baked naan bread with garlic butter', 3.49, 'Breads', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=300&h=200&fit=crop', 1);
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (3, 1, 'Paneer Tikka', 'Grilled cottage cheese with aromatic spices', 12.99, 'Appetizer', 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=300&h=200&fit=crop', 1);

-- Sakura Sushi Bar (2)
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (5, 2, 'Dragon Roll', 'Tempura shrimp roll topped with avocado and eel sauce', 16.99, 'Sushi Rolls', 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=300&h=200&fit=crop', 1);
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (6, 2, 'Salmon Sashimi', 'Fresh Atlantic salmon sliced to perfection', 18.99, 'Sashimi', 'https://images.unsplash.com/photo-1534256958597-7fe685cbd745?w=300&h=200&fit=crop', 1);
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (7, 2, 'Miso Soup', 'Traditional Japanese soup with tofu and seaweed', 4.49, 'Soups', 'https://images.unsplash.com/photo-1607301405390-d831c242f59b?w=300&h=200&fit=crop', 1);

-- Bella Napoli (3)
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (9, 3, 'Margherita Pizza', 'Classic pizza with fresh mozzarella and basil', 13.99, 'Pizza', 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=300&h=200&fit=crop', 1);
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (10, 3, 'Fettuccine Alfredo', 'Creamy parmesan pasta with a hint of garlic', 15.99, 'Pasta', 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=300&h=200&fit=crop', 1);
INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
VALUES (11, 3, 'Tiramisu', 'Traditional Italian coffee-flavored dessert', 8.99, 'Desserts', 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=300&h=200&fit=crop', 1);

-- Commit transaction
COMMIT;
