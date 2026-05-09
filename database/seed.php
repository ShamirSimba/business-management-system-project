<?php
/**
 * Database Seed Script for BMS
 * 
 * This file initializes the BMS database with sample data.
 * It should be run after creating the database using bms_schema.sql
 * 
 * Usage: php seed.php
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    die("Database connection failed\n");
}

try {
    // Check if data already exists
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "Database already has data. Skipping seed.\n";
        exit(0);
    }

    echo "Seeding database...\n";

    // Insert admin user with password hash
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $admin_role = 'admin';
    $stmt->bind_param("ssss", $name, $email, $admin_password, $admin_role);
    $name = 'Admin User';
    $email = 'admin@bms.com';
    $stmt->execute();
    $admin_id = $conn->insert_id;
    $stmt->close();
    echo "✓ Admin user created (email: admin@bms.com, password: admin123)\n";

    // Insert sample business
    $stmt = $conn->prepare("INSERT INTO businesses (user_id, name, type, description, status) VALUES (?, ?, ?, ?, ?)");
    $user_id = $admin_id;
    $biz_name = 'Sample Retail Business';
    $biz_type = 'retail';
    $biz_desc = 'A sample retail business for demonstration';
    $biz_status = 'active';
    $stmt->bind_param("issss", $user_id, $biz_name, $biz_type, $biz_desc, $biz_status);
    $stmt->execute();
    $business_id = $conn->insert_id;
    $stmt->close();
    echo "✓ Sample business created\n";

    // Insert sample products
    $products = [
        ['name' => 'Product A', 'category' => 'Electronics', 'cost' => 50.00, 'selling' => 75.00, 'stock' => 100, 'threshold' => 10],
        ['name' => 'Product B', 'category' => 'Clothing', 'cost' => 20.00, 'selling' => 35.00, 'stock' => 50, 'threshold' => 5],
        ['name' => 'Product C', 'category' => 'Books', 'cost' => 10.00, 'selling' => 15.00, 'stock' => 200, 'threshold' => 20]
    ];

    $stmt = $conn->prepare("INSERT INTO products (business_id, name, category, cost_price, selling_price, stock_qty, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($products as $product) {
        $biz_id = $business_id;
        $stmt->bind_param("issddi", $biz_id, $product['name'], $product['category'], $product['cost'], $product['selling'], $product['stock'], $product['threshold']);
        $stmt->execute();
    }
    $stmt->close();
    echo "✓ Sample products created (3 products)\n";

    // Insert sample investment
    $stmt = $conn->prepare("INSERT INTO investments (business_id, amount, type, note, date) VALUES (?, ?, ?, ?, ?)");
    $biz_id = $business_id;
    $amount = 5000.00;
    $inv_type = 'capital';
    $note = 'Initial capital investment';
    $date = date('Y-m-d');
    $stmt->bind_param("idsss", $biz_id, $amount, $inv_type, $note, $date);
    $stmt->execute();
    $stmt->close();
    echo "✓ Sample investment created\n";

    echo "\n✅ Database seeding completed successfully!\n";
    echo "Admin credentials:\n";
    echo "  Email: admin@bms.com\n";
    echo "  Password: admin123\n";
    echo "\nIMPORTANT: Change admin password after first login!\n";

} catch (Exception $e) {
    echo "❌ Error during seeding: " . $e->getMessage() . "\n";
    exit(1);
}
