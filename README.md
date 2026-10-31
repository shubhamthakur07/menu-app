# QRMenu - Multi-Restaurant Digital QR Menu & Ordering System

A full-stack multi-tenant QR code digital menu and food ordering platform built with **Laravel 11**, **Vue.js 3**, **Tailwind CSS**, and **MySQL (phpMyAdmin)**.

---

## 🌟 Key Features

### 1. Multi-Restaurant / Multi-Hotel Platform
- Har hotel/restaurant owner ka apna alag account aur isolated menu hota hai.
- Unique slug-based digital menu URLs (e.g. `/r/royal-spice`, `/r/green-garden`).
- Different currency, address, phone, logo, banner, and tax settings for each restaurant.

### 2. Hotel Owner Dashboard (`/owner/dashboard`)
- **Live Orders (Kitchen / POS)**: Real-time orders tracking (Pending ➔ Accepted ➔ In Kitchen Preparing ➔ Ready ➔ Served/Completed).
- **Menu Items Management**: Add dishes, set prices, mark Food Type (Veg / Non-Veg / Beverage), add dish photos, toggle Instant Availability (**In Stock** vs **Sold Out**).
- **Categories**: Create and organize categories (Starters, Main Course, Breads, Beverages, Desserts).
- **Table QR Code Generator**: Generate table-specific QR codes (Table 1, Table 2, Table N) ready for printing on table standees.
- **Payment & UPI Configuration**: Set up restaurant UPI ID (e.g. `royalspice@upi`) so customers can pay directly via GPay / PhonePe / Paytm / UPI.

### 3. Customer QR Scan & Ordering Flow (`/r/{slug}?table={number}`)
- Scan QR code from mobile camera.
- Automatically selects the table number (e.g. Table 4).
- Fast, responsive digital menu with search bar and **Veg Only** filter.
- Add dishes to cart, adjust quantities.
- **Checkout Modal**: Enter Name, Phone number, and cooking notes.
- **Payment Setup**:
  1. **UPI Instant Payment**: Shows dynamic QR code for the exact amount and owner's UPI ID, plus direct UPI app intent link.
  2. **Pay at Counter / Cash**: Option to pay after dining.
- **Live Order Tracking (`/order-track/{orderCode}`)**: Real-time progress bar from kitchen to table with payment receipt.

---

## 🚀 How to Run & Access

- **Web Application URL**: [http://localhost:8008](http://localhost:8008)
- **phpMyAdmin URL**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
  - **Database Name**: `menu_app_db`

---

## 🔑 Demo Hotel Owner Accounts

| Restaurant Name | Owner Email | Password | Cuisine |
|---|---|---|---|
| **Royal Spice Palace** | `rajesh@royalspice.com` | `password123` | Mughlai & North Indian |
| **Green Garden Cafe** | `pooja@greengarden.com` | `password123` | 100% Pure Veg European Bistro |

---

## 📱 Sample Customer QR URLs

- **Royal Spice (Table 1)**: `http://localhost:8008/r/royal-spice?table=1`
- **Royal Spice (Table 5)**: `http://localhost:8008/r/royal-spice?table=5`
- **Green Garden Cafe (Table 3)**: `http://localhost:8008/r/green-garden?table=3`
