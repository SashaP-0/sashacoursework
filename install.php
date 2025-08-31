<?php

$servername = 'localhost';
$username = 'root';
$password= '';
$conn = new PDO("mysql:host=$servername", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create and use database
$conn->exec("CREATE DATABASE IF NOT EXISTS BakeryDB");
$conn->exec("USE BakeryDB");

// Drop tables in correct order
$conn->exec("DROP TABLE IF EXISTS tblorders");
$conn->exec("DROP TABLE IF EXISTS tblbasket");
$conn->exec("DROP TABLE IF EXISTS tblitems");
$conn->exec("DROP TABLE IF EXISTS tblusers");
$conn->exec("DROP TABLE IF EXISTS tblareas");

// Create tblusers
$conn->exec("CREATE TABLE tblusers (
  userID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(16) NOT NULL,
  password VARCHAR(225) NOT NULL,
  forename VARCHAR(20),
  surname VARCHAR(20),
  email VARCHAR(50) NOT NULL UNIQUE,
  addressline VARCHAR(50),
  postcode VARCHAR(15), 
  deliveryarea INT(2), 
  phonenumber VARCHAR(20),
  role TINYINT(1),
  dob DATE NOT NULL
)");

// Create tblitems
$conn->exec("CREATE TABLE tblitems (
  itemID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  itemname VARCHAR(50) NOT NULL,
  stock INT(4) NOT NULL,
  itemprice DECIMAL(5,2) NOT NULL,
  image VARCHAR(100),
  category VARCHAR(30),
  dietryrequirements VARCHAR(50),
  description VARCHAR(1000)
)");

// Create tblbasket
$conn->exec("CREATE TABLE tblbasket (
  basketID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  itemID INT(4) UNSIGNED,
  userID INT(5) UNSIGNED,
  quantity INT(3) NOT NULL,
  FOREIGN KEY (itemID) REFERENCES tblitems(itemID),
  FOREIGN KEY (userID) REFERENCES tblusers(userID)
)");

// Create tblorders
$conn->exec("CREATE TABLE tblorders (
  orderID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  userID INT(5) UNSIGNED,
  orderdate DATE NOT NULL,
  status VARCHAR(20),
  deliveryoption INT(1),
  total DECIMAL(7,2),
  FOREIGN KEY (userID) REFERENCES tblusers(userID)
)");

// Create tblareas
$conn->exec("CREATE TABLE tblareas (
  deliveryarea INT(2) PRIMARY KEY,
  areaname VARCHAR(30),
  deliveryprice DECIMAL(4,2),
  delivertype INT(1),
  deliveryday INT(1)
)");

// Insert sample records
$conn->exec("INSERT INTO tblusers (username, password, forename, surname, email, addressline, postcode, deliveryarea, phonenumber, role, dob)
VALUES ('admin', 'adminpass', 'Admin', 'User', 'admin@bakery.com', '1 Main St', 'AB12 3CD', 1, '01234567890', 1, '1990-01-01')");

$conn->exec("INSERT INTO tblitems (itemname, stock, itemprice, image, category, dietryrequirements, description)
VALUES 
('Sourdough Bread', 20, 3.50, 'sourdough.jpg', 'Bread', 'Vegan', 'Traditional sourdough loaf.'),
('Cupcake', 50, 1.20, 'cupcake.jpg', 'Cake', 'Vegetarian', 'Vanilla cupcake with icing.'),
('Brownie', 30, 2.00, 'brownie.jpg', 'Cake', 'Gluten-Free', 'Rich chocolate brownie.')");

$conn->exec("INSERT INTO tblareas (deliveryarea, areaname, deliveryprice, delivertype, deliveryday)
VALUES 
(1, 'Local', 2.50, 1, 5),
(2, 'Extended', 5.00, 2, 7);");



