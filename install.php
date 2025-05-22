
<?php
   $servername = 'localhost';
   $username = 'root';
   $password= '';
$conn = new PDO("mysql:host=$servername", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "CREATE DATABASE IF NOT EXISTS Library";
$conn->exec($sql);
$sql = "USE Library";
$conn->exec($sql);
echo "DB created successfully";

// Drop tables in the correct order to avoid foreign key conflicts 
$conn->exec("DROP TABLE IF EXISTS tblusers");
$conn->exec("DROP TABLE IF EXISTS tblbooks");
$conn->exec("DROP TABLE IF EXISTS tblusers");

// users table //
$stmt = $conn->prepare("DROP TABLE IF EXISTS tblusers;
CREATE TABLE tblusers 
(userID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(16) NOT NULL,
password VARCHAR(225) NOT NULL,
forename VARCHAR(20),
surname VARCHAR(20),
email VARCHAR(50) NOT NULL UNIQUE,
addressline VARCHAR(50),
postcode VARCHAR(15), 
deliveryarea INT(2), 
phonenumber VARCHAR(20),
role TINYINT(1)
dob DATE NOT NULL
");
$stmt->execute();
$stmt->closeCursor();
echo"<br>tblusers created";

// items table //
$stmt = $conn->prepare("DROP TABLE IF EXISTS tblitems;
CREATE TABLE tblitems 
(itemID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
itemname VARCHAR(20) NOT NULL,
stock INT(4) NOT NULL,
itemprice DECIMAL(5,2) NOT NULL,
image VARCHAR(100),
category -------------------------------, 
dietryrequirements -------------------------------,
description VARCHAR(1000)
");
$stmt->execute();
$stmt->closeCursor();
echo"<br>tblitems created";

// basket table //
$stmt = $conn->prepare("DROP TABLE IF EXISTS tblbasket;
CREATE TABLE tblbasket
(loanID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
itemID INT(4),
userID INT(5),
");
$stmt->execute();
$stmt->closeCursor();
echo"<br>tblbasket created";

// orders table //
$stmt = $conn->prepare("DROP TABLE IF EXISTS tblorders;
CREATE TABLE tblorders
(loanID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
itemID INT(4),
numitems INT(2),
recurringorder INT(1),
status INT(1),
deliveryoption INT(1)
"); 
$stmt->execute();
$stmt->closeCursor();
echo"<br>tblorders created";

// delivery areas table //
$stmt = $conn->prepare("DROP TABLE IF EXISTS tblareas;
CREATE TABLE tblareas
(deliveryarea INT(2),
areaname VARCHAR(30),
deliveryprice DECIMAL(4,2),
delivertype INT(1),
deliveryday INT(1)
");
$stmt->execute();
$stmt->closeCursor();
echo"<br>tblareas created";


