# 🍰 Sweet Mart

Sweet Mart is an online dessert and gift store developed as an e-business system. 
The system allows customers to browse desserts, add products to a shopping cart, 
create custom sweet boxes, place orders, and manage their order information.

## 📌 Project Overview

Sweet Mart provides an easy and convenient way for customers to purchase 
desserts and gift boxes online. The system includes product browsing, 
shopping cart management, checkout, order processing, and custom sweet box creation.

## ✨ Main Features

### 👤 Customer Features
- Customer registration and login
- Browse dessert categories
- Search and filter products
- View product details
- Select flavours and sizes
- Add products to cart
- Update and remove cart items
- Create a custom sweet box
- Checkout and place orders
- Select payment method
- View order history
- Contact Sweet Mart

### 🔐 Admin Features
- Admin login
- Manage products
- Manage categories
- Manage customer orders
- View order details
- Update order status
- View customer information

## 🛍️ Product Categories

- 🎂 Cakes
- 🧁 Cupcakes
- 🍫 Brownies
- 🍪 Cookies
- 🍩 Donuts
- 🎁 Gift Boxes

## 🎁 Custom Sweet Box

Sweet Mart includes a special **Custom Sweet Box** feature that allows 
customers to create their own dessert gift box by selecting the box size, 
desserts, occasion, and a personalized message.

## 🗄️ Database

The project uses a MySQL database named `sweetmart`.

Main database tables include:

- Users
- Categories
- Products
- Cart
- Cart Items
- Orders
- Order Items
- Custom Boxes
- Custom Box Items
- Payments
- Contact Messages

## 💻 Technologies Used

- HTML5
- CSS3
- JavaScript
- Bootstrap
- PHP
- MySQL
- XAMPP
- Git
- GitHub

## 📁 Project Structure

```text
Sweet Mart/
│
├── admin/
├── assets/
│   ├── css/
│   ├── images/
│   │   └── products/
│   └── js/
│
├── db/
│   └── sweetmart.sql
│
├── includes/
│   ├── admin_sidebar.php
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
│
├── about.php
├── cart.php
├── cart_action.php
├── checkout.php
├── contact.php
├── custom_box.php
├── index.php
├── login.php
├── logout.php
├── my_orders.php
├── order_confirmation.php
├── product.php
└── shop.php