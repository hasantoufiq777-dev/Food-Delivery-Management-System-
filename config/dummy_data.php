<?php
/**
 * Dummy Data Configuration
 * Food Delivery Management System
 * 
 * All static data arrays used across the application.
 * Replace with MySQL queries when wiring up the database.
 */

// ─── Restaurants ────────────────────────────────────────────────
$restaurants = [
    [
        'id' => 1,
        'name' => 'The Spice Garden',
        'cuisine' => 'Indian',
        'rating' => 4.7,
        'address' => '42 Curry Lane, Downtown',
        'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop',
        'phone' => '+1-555-0101',
        'status' => 'active',
        'email' => 'spicegarden@example.com',
        'password' => 'password',
        'created_at' => '2025-01-15 09:00:00'
    ],
    [
        'id' => 2,
        'name' => 'Sakura Sushi Bar',
        'cuisine' => 'Japanese',
        'rating' => 4.9,
        'address' => '88 Blossom Avenue, Midtown',
        'image_url' => 'https://images.unsplash.com/photo-1579027989536-b7b1f875659b?w=400&h=300&fit=crop',
        'phone' => '+1-555-0102',
        'status' => 'active',
        'email' => 'sakura@example.com',
        'password' => 'password',
        'created_at' => '2025-02-10 10:30:00'
    ],
    [
        'id' => 3,
        'name' => 'Bella Napoli',
        'cuisine' => 'Italian',
        'rating' => 4.5,
        'address' => '15 Pasta Street, Little Italy',
        'image_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=400&h=300&fit=crop',
        'phone' => '+1-555-0103',
        'status' => 'active',
        'email' => 'bellanapoli@example.com',
        'password' => 'password',
        'created_at' => '2025-03-05 11:00:00'
    ],
    [
        'id' => 4,
        'name' => 'Dragon Wok',
        'cuisine' => 'Chinese',
        'rating' => 4.3,
        'address' => '200 Noodle Road, Chinatown',
        'image_url' => 'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=400&h=300&fit=crop',
        'phone' => '+1-555-0104',
        'status' => 'active',
        'email' => 'dragonwok@example.com',
        'password' => 'password',
        'created_at' => '2025-04-20 08:45:00'
    ],
    [
        'id' => 5,
        'name' => 'Le Petit Bistro',
        'cuisine' => 'French',
        'rating' => 4.8,
        'address' => '7 Rue de la Paix, Uptown',
        'image_url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&h=300&fit=crop',
        'phone' => '+1-555-0105',
        'status' => 'active',
        'email' => 'lepetitbistro@example.com',
        'password' => 'password',
        'created_at' => '2025-05-01 12:00:00'
    ]
];

// ─── Menu Items ─────────────────────────────────────────────────
$menu_items = [
    // The Spice Garden (restaurant_id: 1)
    ['id' => 1,  'restaurant_id' => 1, 'name' => 'Butter Chicken',     'price' => 14.99, 'category' => 'Main Course',  'description' => 'Creamy tomato-based curry with tender chicken pieces', 'image_url' => 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 2,  'restaurant_id' => 1, 'name' => 'Garlic Naan',        'price' => 3.49,  'category' => 'Breads',       'description' => 'Fresh-baked naan bread with garlic butter',           'image_url' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 3,  'restaurant_id' => 1, 'name' => 'Paneer Tikka',       'price' => 12.99, 'category' => 'Appetizer',    'description' => 'Grilled cottage cheese with aromatic spices',         'image_url' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 4,  'restaurant_id' => 1, 'name' => 'Mango Lassi',        'price' => 4.99,  'category' => 'Beverages',    'description' => 'Refreshing yogurt drink blended with mango pulp',     'image_url' => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=300&h=200&fit=crop', 'available' => true],

    // Sakura Sushi Bar (restaurant_id: 2)
    ['id' => 5,  'restaurant_id' => 2, 'name' => 'Dragon Roll',        'price' => 16.99, 'category' => 'Sushi Rolls',  'description' => 'Tempura shrimp roll topped with avocado and eel sauce','image_url' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 6,  'restaurant_id' => 2, 'name' => 'Salmon Sashimi',     'price' => 18.99, 'category' => 'Sashimi',      'description' => 'Fresh Atlantic salmon sliced to perfection',          'image_url' => 'https://images.unsplash.com/photo-1534256958597-7fe685cbd745?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 7,  'restaurant_id' => 2, 'name' => 'Miso Soup',          'price' => 4.49,  'category' => 'Soups',        'description' => 'Traditional Japanese soup with tofu and seaweed',     'image_url' => 'https://images.unsplash.com/photo-1607301405390-d831c242f59b?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 8,  'restaurant_id' => 2, 'name' => 'Matcha Ice Cream',   'price' => 6.99,  'category' => 'Desserts',     'description' => 'Authentic green tea flavored ice cream',              'image_url' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=300&h=200&fit=crop', 'available' => false],

    // Bella Napoli (restaurant_id: 3)
    ['id' => 9,  'restaurant_id' => 3, 'name' => 'Margherita Pizza',   'price' => 13.99, 'category' => 'Pizza',        'description' => 'Classic pizza with fresh mozzarella and basil',       'image_url' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 10, 'restaurant_id' => 3, 'name' => 'Fettuccine Alfredo', 'price' => 15.99, 'category' => 'Pasta',        'description' => 'Creamy parmesan pasta with a hint of garlic',         'image_url' => 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 11, 'restaurant_id' => 3, 'name' => 'Tiramisu',           'price' => 8.99,  'category' => 'Desserts',     'description' => 'Traditional Italian coffee-flavored dessert',         'image_url' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 12, 'restaurant_id' => 3, 'name' => 'Bruschetta',         'price' => 7.99,  'category' => 'Appetizer',    'description' => 'Toasted bread topped with tomatoes and fresh basil',   'image_url' => 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=300&h=200&fit=crop', 'available' => true],

    // Dragon Wok (restaurant_id: 4)
    ['id' => 13, 'restaurant_id' => 4, 'name' => 'Kung Pao Chicken',   'price' => 13.49, 'category' => 'Main Course',  'description' => 'Spicy stir-fried chicken with peanuts and chili',     'image_url' => 'https://images.unsplash.com/photo-1525755662778-989d0524087e?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 14, 'restaurant_id' => 4, 'name' => 'Spring Rolls',       'price' => 6.99,  'category' => 'Appetizer',    'description' => 'Crispy fried rolls with vegetable filling',           'image_url' => 'https://images.unsplash.com/photo-1548507346-b3e9ad6a1ba5?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 15, 'restaurant_id' => 4, 'name' => 'Fried Rice Special', 'price' => 11.99, 'category' => 'Rice',         'description' => 'Wok-fried rice with shrimp, egg, and vegetables',     'image_url' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 16, 'restaurant_id' => 4, 'name' => 'Hot & Sour Soup',    'price' => 5.49,  'category' => 'Soups',        'description' => 'Traditional Chinese soup with tofu and mushrooms',    'image_url' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=300&h=200&fit=crop', 'available' => true],

    // Le Petit Bistro (restaurant_id: 5)
    ['id' => 17, 'restaurant_id' => 5, 'name' => 'Coq au Vin',         'price' => 22.99, 'category' => 'Main Course',  'description' => 'Braised chicken in red wine with mushrooms and pearl onions', 'image_url' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 18, 'restaurant_id' => 5, 'name' => 'French Onion Soup',  'price' => 9.99,  'category' => 'Soups',        'description' => 'Caramelized onion soup with gruyère crouton',         'image_url' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 19, 'restaurant_id' => 5, 'name' => 'Crème Brûlée',       'price' => 10.99, 'category' => 'Desserts',     'description' => 'Vanilla custard with a crisp caramelized sugar top',   'image_url' => 'https://images.unsplash.com/photo-1470124182917-cc6e71b22ecc?w=300&h=200&fit=crop', 'available' => true],
    ['id' => 20, 'restaurant_id' => 5, 'name' => 'Croissant Basket',   'price' => 7.49,  'category' => 'Breads',       'description' => 'Assorted freshly baked butter croissants',            'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038024a?w=300&h=200&fit=crop', 'available' => true],
];

// ─── Customers ──────────────────────────────────────────────────
$customers = [
    ['id' => 1,  'name' => 'Alice Johnson',    'email' => 'alice@example.com',    'password' => 'password', 'phone' => '+1-555-1001', 'address' => '123 Maple Street, Apt 4B',     'created_at' => '2025-01-10 08:00:00'],
    ['id' => 2,  'name' => 'Bob Martinez',      'email' => 'bob@example.com',      'password' => 'password', 'phone' => '+1-555-1002', 'address' => '456 Oak Avenue, Suite 200',     'created_at' => '2025-01-15 09:30:00'],
    ['id' => 3,  'name' => 'Carol Chen',         'email' => 'carol@example.com',    'password' => 'password', 'phone' => '+1-555-1003', 'address' => '789 Pine Road',                 'created_at' => '2025-02-01 10:00:00'],
    ['id' => 4,  'name' => 'David Kim',          'email' => 'david@example.com',    'password' => 'password', 'phone' => '+1-555-1004', 'address' => '321 Elm Boulevard, Floor 3',    'created_at' => '2025-02-14 11:15:00'],
    ['id' => 5,  'name' => 'Eva Rodriguez',      'email' => 'eva@example.com',      'password' => 'password', 'phone' => '+1-555-1005', 'address' => '654 Cedar Lane',                'created_at' => '2025-03-01 14:00:00'],
    ['id' => 6,  'name' => 'Frank Wilson',       'email' => 'frank@example.com',    'password' => 'password', 'phone' => '+1-555-1006', 'address' => '987 Birch Court',               'created_at' => '2025-03-20 16:30:00'],
    ['id' => 7,  'name' => 'Grace Liu',          'email' => 'grace@example.com',    'password' => 'password', 'phone' => '+1-555-1007', 'address' => '147 Willow Way, Apt 12',       'created_at' => '2025-04-05 07:45:00'],
    ['id' => 8,  'name' => 'Henry Patel',        'email' => 'henry@example.com',    'password' => 'password', 'phone' => '+1-555-1008', 'address' => '258 Spruce Drive',              'created_at' => '2025-04-18 13:00:00'],
    ['id' => 9,  'name' => 'Iris Thompson',      'email' => 'iris@example.com',     'password' => 'password', 'phone' => '+1-555-1009', 'address' => '369 Ash Circle, Unit 5C',      'created_at' => '2025-05-02 09:00:00'],
    ['id' => 10, 'name' => 'Jake Nguyen',        'email' => 'jake@example.com',     'password' => 'password', 'phone' => '+1-555-1010', 'address' => '480 Redwood Place',             'created_at' => '2025-05-15 11:30:00'],
];

// ─── Delivery Agents ────────────────────────────────────────────
$agents = [
    ['id' => 1, 'name' => 'Marcus Reed',     'email' => 'marcus@example.com',  'password' => 'password', 'phone' => '+1-555-2001', 'vehicle' => 'Motorcycle', 'status' => 'available', 'rating' => 4.8, 'deliveries_completed' => 234, 'created_at' => '2025-01-05 08:00:00'],
    ['id' => 2, 'name' => 'Priya Sharma',    'email' => 'priya@example.com',   'password' => 'password', 'phone' => '+1-555-2002', 'vehicle' => 'Bicycle',    'status' => 'busy',      'rating' => 4.6, 'deliveries_completed' => 189, 'created_at' => '2025-01-12 09:00:00'],
    ['id' => 3, 'name' => 'Tomás Herrera',   'email' => 'tomas@example.com',   'password' => 'password', 'phone' => '+1-555-2003', 'vehicle' => 'Car',        'status' => 'available', 'rating' => 4.9, 'deliveries_completed' => 312, 'created_at' => '2025-02-01 10:00:00'],
    ['id' => 4, 'name' => 'Aisha Johnson',   'email' => 'aisha@example.com',   'password' => 'password', 'phone' => '+1-555-2004', 'vehicle' => 'Motorcycle', 'status' => 'busy',      'rating' => 4.7, 'deliveries_completed' => 156, 'created_at' => '2025-03-15 11:00:00'],
    ['id' => 5, 'name' => 'Liam O\'Brien',   'email' => 'liam@example.com',    'password' => 'password', 'phone' => '+1-555-2005', 'vehicle' => 'Bicycle',    'status' => 'available', 'rating' => 4.5, 'deliveries_completed' => 98,  'created_at' => '2025-04-01 12:00:00'],
];

// ─── Orders ─────────────────────────────────────────────────────
$orders = [
    [
        'id' => 1001, 'customer_id' => 1, 'restaurant_id' => 1, 'agent_id' => 1,
        'items' => [
            ['menu_item_id' => 1, 'name' => 'Butter Chicken',  'price' => 14.99, 'quantity' => 2],
            ['menu_item_id' => 2, 'name' => 'Garlic Naan',     'price' => 3.49,  'quantity' => 3],
        ],
        'subtotal' => 40.45, 'delivery_fee' => 3.99, 'total' => 44.44,
        'status' => 'delivered', 'payment_method' => 'Credit Card',
        'delivery_address' => '123 Maple Street, Apt 4B',
        'created_at' => '2025-06-01 12:30:00', 'delivered_at' => '2025-06-01 13:15:00'
    ],
    [
        'id' => 1002, 'customer_id' => 2, 'restaurant_id' => 2, 'agent_id' => 2,
        'items' => [
            ['menu_item_id' => 5, 'name' => 'Dragon Roll',     'price' => 16.99, 'quantity' => 1],
            ['menu_item_id' => 6, 'name' => 'Salmon Sashimi',  'price' => 18.99, 'quantity' => 1],
            ['menu_item_id' => 7, 'name' => 'Miso Soup',       'price' => 4.49,  'quantity' => 2],
        ],
        'subtotal' => 44.96, 'delivery_fee' => 3.99, 'total' => 48.95,
        'status' => 'out_for_delivery', 'payment_method' => 'PayPal',
        'delivery_address' => '456 Oak Avenue, Suite 200',
        'created_at' => '2025-06-05 18:00:00', 'delivered_at' => null
    ],
    [
        'id' => 1003, 'customer_id' => 3, 'restaurant_id' => 3, 'agent_id' => 3,
        'items' => [
            ['menu_item_id' => 9,  'name' => 'Margherita Pizza',   'price' => 13.99, 'quantity' => 2],
            ['menu_item_id' => 11, 'name' => 'Tiramisu',           'price' => 8.99,  'quantity' => 1],
        ],
        'subtotal' => 36.97, 'delivery_fee' => 3.99, 'total' => 40.96,
        'status' => 'preparing', 'payment_method' => 'Credit Card',
        'delivery_address' => '789 Pine Road',
        'created_at' => '2025-06-06 19:30:00', 'delivered_at' => null
    ],
    [
        'id' => 1004, 'customer_id' => 4, 'restaurant_id' => 4, 'agent_id' => null,
        'items' => [
            ['menu_item_id' => 13, 'name' => 'Kung Pao Chicken',   'price' => 13.49, 'quantity' => 1],
            ['menu_item_id' => 15, 'name' => 'Fried Rice Special', 'price' => 11.99, 'quantity' => 1],
            ['menu_item_id' => 14, 'name' => 'Spring Rolls',       'price' => 6.99,  'quantity' => 2],
        ],
        'subtotal' => 39.46, 'delivery_fee' => 3.99, 'total' => 43.45,
        'status' => 'placed', 'payment_method' => 'Cash on Delivery',
        'delivery_address' => '321 Elm Boulevard, Floor 3',
        'created_at' => '2025-06-07 10:15:00', 'delivered_at' => null
    ],
    [
        'id' => 1005, 'customer_id' => 5, 'restaurant_id' => 5, 'agent_id' => 4,
        'items' => [
            ['menu_item_id' => 17, 'name' => 'Coq au Vin',        'price' => 22.99, 'quantity' => 1],
            ['menu_item_id' => 18, 'name' => 'French Onion Soup', 'price' => 9.99,  'quantity' => 1],
            ['menu_item_id' => 19, 'name' => 'Crème Brûlée',      'price' => 10.99, 'quantity' => 2],
        ],
        'subtotal' => 54.96, 'delivery_fee' => 4.99, 'total' => 59.95,
        'status' => 'confirmed', 'payment_method' => 'Credit Card',
        'delivery_address' => '654 Cedar Lane',
        'created_at' => '2025-06-07 11:00:00', 'delivered_at' => null
    ],
    [
        'id' => 1006, 'customer_id' => 1, 'restaurant_id' => 2, 'agent_id' => 3,
        'items' => [
            ['menu_item_id' => 5, 'name' => 'Dragon Roll',  'price' => 16.99, 'quantity' => 2],
            ['menu_item_id' => 7, 'name' => 'Miso Soup',    'price' => 4.49,  'quantity' => 1],
        ],
        'subtotal' => 38.47, 'delivery_fee' => 3.99, 'total' => 42.46,
        'status' => 'delivered', 'payment_method' => 'PayPal',
        'delivery_address' => '123 Maple Street, Apt 4B',
        'created_at' => '2025-05-28 13:00:00', 'delivered_at' => '2025-05-28 13:45:00'
    ],
    [
        'id' => 1007, 'customer_id' => 6, 'restaurant_id' => 1, 'agent_id' => 1,
        'items' => [
            ['menu_item_id' => 3, 'name' => 'Paneer Tikka', 'price' => 12.99, 'quantity' => 1],
            ['menu_item_id' => 4, 'name' => 'Mango Lassi',  'price' => 4.99,  'quantity' => 2],
        ],
        'subtotal' => 22.97, 'delivery_fee' => 3.99, 'total' => 26.96,
        'status' => 'delivered', 'payment_method' => 'Credit Card',
        'delivery_address' => '987 Birch Court',
        'created_at' => '2025-05-30 20:00:00', 'delivered_at' => '2025-05-30 20:40:00'
    ],
    [
        'id' => 1008, 'customer_id' => 7, 'restaurant_id' => 3, 'agent_id' => 2,
        'items' => [
            ['menu_item_id' => 10, 'name' => 'Fettuccine Alfredo', 'price' => 15.99, 'quantity' => 1],
            ['menu_item_id' => 12, 'name' => 'Bruschetta',         'price' => 7.99,  'quantity' => 1],
        ],
        'subtotal' => 23.98, 'delivery_fee' => 3.99, 'total' => 27.97,
        'status' => 'cancelled', 'payment_method' => 'Credit Card',
        'delivery_address' => '147 Willow Way, Apt 12',
        'created_at' => '2025-06-02 17:30:00', 'delivered_at' => null
    ],
    [
        'id' => 1009, 'customer_id' => 8, 'restaurant_id' => 4, 'agent_id' => 5,
        'items' => [
            ['menu_item_id' => 13, 'name' => 'Kung Pao Chicken',   'price' => 13.49, 'quantity' => 2],
            ['menu_item_id' => 16, 'name' => 'Hot & Sour Soup',    'price' => 5.49,  'quantity' => 1],
        ],
        'subtotal' => 32.47, 'delivery_fee' => 3.99, 'total' => 36.46,
        'status' => 'delivered', 'payment_method' => 'Cash on Delivery',
        'delivery_address' => '258 Spruce Drive',
        'created_at' => '2025-06-03 12:00:00', 'delivered_at' => '2025-06-03 12:50:00'
    ],
    [
        'id' => 1010, 'customer_id' => 9, 'restaurant_id' => 5, 'agent_id' => 4,
        'items' => [
            ['menu_item_id' => 17, 'name' => 'Coq au Vin',     'price' => 22.99, 'quantity' => 1],
            ['menu_item_id' => 20, 'name' => 'Croissant Basket','price' => 7.49,  'quantity' => 1],
        ],
        'subtotal' => 30.48, 'delivery_fee' => 4.99, 'total' => 35.47,
        'status' => 'ready', 'payment_method' => 'PayPal',
        'delivery_address' => '369 Ash Circle, Unit 5C',
        'created_at' => '2025-06-06 14:00:00', 'delivered_at' => null
    ],
    [
        'id' => 1011, 'customer_id' => 10, 'restaurant_id' => 1, 'agent_id' => null,
        'items' => [
            ['menu_item_id' => 1, 'name' => 'Butter Chicken', 'price' => 14.99, 'quantity' => 1],
            ['menu_item_id' => 2, 'name' => 'Garlic Naan',    'price' => 3.49,  'quantity' => 2],
        ],
        'subtotal' => 21.97, 'delivery_fee' => 3.99, 'total' => 25.96,
        'status' => 'placed', 'payment_method' => 'Credit Card',
        'delivery_address' => '480 Redwood Place',
        'created_at' => '2025-06-07 11:30:00', 'delivered_at' => null
    ],
    [
        'id' => 1012, 'customer_id' => 2, 'restaurant_id' => 3, 'agent_id' => 1,
        'items' => [
            ['menu_item_id' => 9,  'name' => 'Margherita Pizza', 'price' => 13.99, 'quantity' => 1],
            ['menu_item_id' => 10, 'name' => 'Fettuccine Alfredo','price' => 15.99, 'quantity' => 1],
            ['menu_item_id' => 11, 'name' => 'Tiramisu',          'price' => 8.99,  'quantity' => 2],
        ],
        'subtotal' => 47.96, 'delivery_fee' => 3.99, 'total' => 51.95,
        'status' => 'out_for_delivery', 'payment_method' => 'Credit Card',
        'delivery_address' => '456 Oak Avenue, Suite 200',
        'created_at' => '2025-06-06 20:00:00', 'delivered_at' => null
    ],
    [
        'id' => 1013, 'customer_id' => 3, 'restaurant_id' => 2, 'agent_id' => 5,
        'items' => [
            ['menu_item_id' => 6, 'name' => 'Salmon Sashimi', 'price' => 18.99, 'quantity' => 2],
        ],
        'subtotal' => 37.98, 'delivery_fee' => 3.99, 'total' => 41.97,
        'status' => 'delivered', 'payment_method' => 'PayPal',
        'delivery_address' => '789 Pine Road',
        'created_at' => '2025-05-25 19:00:00', 'delivered_at' => '2025-05-25 19:35:00'
    ],
    [
        'id' => 1014, 'customer_id' => 5, 'restaurant_id' => 4, 'agent_id' => 3,
        'items' => [
            ['menu_item_id' => 14, 'name' => 'Spring Rolls',      'price' => 6.99,  'quantity' => 3],
            ['menu_item_id' => 15, 'name' => 'Fried Rice Special', 'price' => 11.99, 'quantity' => 2],
        ],
        'subtotal' => 44.95, 'delivery_fee' => 3.99, 'total' => 48.94,
        'status' => 'preparing', 'payment_method' => 'Credit Card',
        'delivery_address' => '654 Cedar Lane',
        'created_at' => '2025-06-07 09:45:00', 'delivered_at' => null
    ],
    [
        'id' => 1015, 'customer_id' => 4, 'restaurant_id' => 5, 'agent_id' => null,
        'items' => [
            ['menu_item_id' => 18, 'name' => 'French Onion Soup', 'price' => 9.99,  'quantity' => 1],
            ['menu_item_id' => 19, 'name' => 'Crème Brûlée',      'price' => 10.99, 'quantity' => 1],
        ],
        'subtotal' => 20.98, 'delivery_fee' => 4.99, 'total' => 25.97,
        'status' => 'placed', 'payment_method' => 'Cash on Delivery',
        'delivery_address' => '321 Elm Boulevard, Floor 3',
        'created_at' => '2025-06-07 12:00:00', 'delivered_at' => null
    ],
];

// ─── Cart (Session-based, default empty) ────────────────────────
$default_cart = [
    'restaurant_id' => null,
    'items' => [],
    'subtotal' => 0,
    'delivery_fee' => 3.99,
    'total' => 3.99,
];

// ─── Status Flow Definitions ────────────────────────────────────
$order_status_flow = ['placed', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'];
$order_status_labels = [
    'placed'           => 'Placed',
    'confirmed'        => 'Confirmed',
    'preparing'        => 'Preparing',
    'ready'            => 'Ready',
    'out_for_delivery'  => 'Out for Delivery',
    'delivered'        => 'Delivered',
    'cancelled'        => 'Cancelled',
];

// ─── Categories ─────────────────────────────────────────────────
$menu_categories = ['Main Course', 'Appetizer', 'Sushi Rolls', 'Sashimi', 'Pizza', 'Pasta', 'Soups', 'Breads', 'Rice', 'Desserts', 'Beverages'];

// ─── Cuisine Types ──────────────────────────────────────────────
$cuisine_types = ['Indian', 'Japanese', 'Italian', 'Chinese', 'French', 'Mexican', 'Thai', 'American'];
