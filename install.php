<?php

$servername = 'localhost';
$username = 'root';
$password= '';
$conn = new PDO("mysql:host=$servername", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create and use database
$conn->exec("CREATE DATABASE IF NOT EXISTS BakeryDB");
$conn->exec("USE BakeryDB");

// Drop tables in correct order (respecting foreign key constraints)
// Drop child tables first, then parent tables
$conn->exec("DROP TABLE IF EXISTS tblreviews");
$conn->exec("DROP TABLE IF EXISTS tblbasket");
$conn->exec("DROP TABLE IF EXISTS tblorders");
$conn->exec("DROP TABLE IF EXISTS tblitems");
$conn->exec("DROP TABLE IF EXISTS tblusers");
$conn->exec("DROP TABLE IF EXISTS tblpostcodes");
$conn->exec("DROP TABLE IF EXISTS tblareas");
$conn->exec("DROP TABLE IF EXISTS tblcategories");

// Create tblcategories
$conn->exec("CREATE TABLE tblcategories (
  categoryID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoryname VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(200),
  displayorder INT(2) DEFAULT 0
)");

// Create tblareas
$conn->exec("CREATE TABLE tblareas (
  deliveryarea INT(2) PRIMARY KEY,
  areaname VARCHAR(30) NOT NULL,
  deliveryprice DECIMAL(4,2) NOT NULL,
  delivertype INT(1) NOT NULL COMMENT '1=Market pickup, 2=Home delivery',
  deliveryday INT(1) NOT NULL COMMENT '1=Monday, 2=Tuesday, etc.'
)");

// Create tblpostcodes
$conn->exec("CREATE TABLE tblpostcodes (
  postcode VARCHAR(10) PRIMARY KEY,
  deliveryarea INT(2) NOT NULL,
  FOREIGN KEY (deliveryarea) REFERENCES tblareas(deliveryarea)
)");

// Create tblusers
$conn->exec("CREATE TABLE tblusers (
  userID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(16) NOT NULL UNIQUE,
  password VARCHAR(60) NOT NULL COMMENT 'PHP password_hash output',
  forename VARCHAR(20) NOT NULL,
  surname VARCHAR(30) NOT NULL,
  email VARCHAR(40) NOT NULL UNIQUE,
  addressline VARCHAR(50),
  postcode VARCHAR(10),
  deliveryarea INT(2),
  phonenumber VARCHAR(20),
  role TINYINT(1) DEFAULT 0 COMMENT '0=Customer, 1=Admin',
  dob DATE NOT NULL,
  created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (deliveryarea) REFERENCES tblareas(deliveryarea),
  FOREIGN KEY (postcode) REFERENCES tblpostcodes(postcode)
)");

// Create tblitems
$conn->exec("CREATE TABLE tblitems (
  itemID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  itemname VARCHAR(50) NOT NULL,
  stock INT(4) NOT NULL DEFAULT 0,
  itemprice DECIMAL(5,2) NOT NULL,
  image VARCHAR(100),
  categoryID INT(3) UNSIGNED NOT NULL,
  dietryrequirements SET('Vegetarian', 'Vegan', 'Gluten-Free', 'Nut-Free', 'Dairy-Free') DEFAULT NULL,
  description VARCHAR(1000),
  is_active BOOLEAN DEFAULT TRUE,
  created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoryID) REFERENCES tblcategories(categoryID)
)");

// Create tblorders
$conn->exec("CREATE TABLE tblorders (
  orderID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  userID INT(5) UNSIGNED NOT NULL,
  orderdate DATE NOT NULL,
  deliverydate DATE NOT NULL,
  status TINYINT(1) DEFAULT 0 COMMENT '0=Pending, 1=Confirmed, 2=In Progress, 3=Ready, 4=Delivered, 5=Cancelled',
  deliveryoption INT(1) NOT NULL COMMENT '1=Market pickup, 2=Home delivery',
  deliveryarea INT(2) NOT NULL,
  total DECIMAL(7,2) NOT NULL,
  recurringorder BOOLEAN DEFAULT FALSE,
  recurring_frequency INT(1) DEFAULT 0 COMMENT '0=One-time, 1=Weekly, 2=Fortnightly, 3=Monthly',
  notes TEXT,
  created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userID) REFERENCES tblusers(userID),
  FOREIGN KEY (deliveryarea) REFERENCES tblareas(deliveryarea)
)");

// Create tblbasket
$conn->exec("CREATE TABLE tblbasket (
  basketID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orderID INT(6) UNSIGNED,
  itemID INT(4) UNSIGNED NOT NULL,
  userID INT(5) UNSIGNED,
  numitems INT(2) NOT NULL DEFAULT 1,
  deliveryarea INT(2) NOT NULL,
  created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (orderID) REFERENCES tblorders(orderID),
  FOREIGN KEY (itemID) REFERENCES tblitems(itemID),
  FOREIGN KEY (userID) REFERENCES tblusers(userID),
  FOREIGN KEY (deliveryarea) REFERENCES tblareas(deliveryarea)
)");

// Create tblreviews
$conn->exec("CREATE TABLE tblreviews (
  reviewID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  itemID INT(4) UNSIGNED NOT NULL,
  userID INT(5) UNSIGNED NOT NULL,
  orderID INT(6) UNSIGNED NOT NULL,
  stars INT(1) NOT NULL CHECK (stars >= 1 AND stars <= 5),
  review TEXT,
  is_approved BOOLEAN DEFAULT FALSE,
  created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (itemID) REFERENCES tblitems(itemID),
  FOREIGN KEY (userID) REFERENCES tblusers(userID),
  FOREIGN KEY (orderID) REFERENCES tblorders(orderID)
)");

// Insert sample categories
$conn->exec("INSERT INTO tblcategories (categoryname, description, displayorder) VALUES 
('Bread', 'Fresh baked breads and rolls', 1),
('Cakes', 'Homemade cakes and pastries', 2),
('Dairy', 'Fresh dairy products', 3),
('Non-Perishables', 'Honey, jams, and preserves', 4),
('Seasonal', 'Seasonal and special items', 5)");

// Insert sample delivery areas
$conn->exec("INSERT INTO tblareas (deliveryarea, areaname, deliveryprice, delivertype, deliveryday) VALUES 
(1, 'Local', 2.50, 1, 5),
(2, 'Extended', 5.00, 2, 7)");

// Insert sample postcodes
$conn->exec("INSERT INTO tblpostcodes (postcode, deliveryarea) VALUES 
('AB12 3CD', 1),
('EF34 5GH', 2)");

// Insert sample admin user
$conn->exec("INSERT INTO tblusers (username, password, forename, surname, email, addressline, postcode, deliveryarea, phonenumber, role, dob) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin@bakery.com', '1 Main St', 'AB12 3CD', 1, '01234567890', 1, '1990-01-01')");

// Insert sample items
$conn->exec("INSERT INTO tblitems (itemname, stock, itemprice, image, categoryID, dietryrequirements, description) VALUES 
('Sourdough Bread', 20, 3.50, 'sourdough.jpg', 1, 'Vegan', 'Traditional sourdough loaf.'),
('Cupcake', 50, 1.20, 'cupcake.jpg', 2, 'Vegetarian', 'Vanilla cupcake with icing.'),
('Brownie', 30, 2.00, 'brownie.jpg', 2, 'Gluten-Free', 'Rich chocolate brownie.')");

echo "Database setup complete! All tables created with sample data.";
?>



