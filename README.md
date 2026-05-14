# 👕 Clothing Inventory System

A secure, role-based clothing inventory management system built with PHP and MySQL. This system provides a comprehensive solution for managing clothing inventory, user accounts, and item approvals across three distinct user roles.

## ✨ Key Features

### 👥 User Roles & Permissions

| Role | Capabilities |
|------|--------------|
| **Super Admin** | Full system oversight, manages admin accounts, resets admin passwords, archives/restores admins |
| **Admin** | Manages inventory items, approves user-submitted items, manages regular user accounts, archives/restores items and users |
| **User** | Views inventory, submits items for admin approval, tracks pending/approved/rejected items |

### 🔐 Security Features

- **SQL Injection Prevention:** MySQLi Prepared Statements for all database queries
- **XSS Protection:** Output escaping across all views
- **Password Security:** `bcrypt` hashing for all user credentials
- **Remember Me Functionality:** Secure database-backed token system for persistent login sessions using selector-validator pattern
- **Action Approval Workflow:** Admin approval required for user-submitted items
- **Archive System:** Soft-delete functionality for items and users

### 🛠️ Admin Management

- Add, archive, and restore admin accounts
- Reset admin passwords
- View all pending user submissions
- Manage user accounts

### 🧑‍💻 User Features

- Submit new clothing items for admin approval
- Track submission status (pending, approved, rejected)
- View approved items in inventory
- Secure login with optional "Remember Me" persistence

## 📦 Tech Stack

| Component | Technology |
|-----------|------------|
| **Frontend** | HTML5, CSS3 (Modern responsive design) |
| **Backend** | PHP (Procedural with modular functions) |
| **Database** | MySQL |
| **Authentication** | Custom session-based with Remember Me cookie persistence |
| **Server Requirements** | XAMPP / WAMP / LAMP with PHP 7.4+ |

## 📁 Project Structure

```
CLOTHING_STORE/
│
├── admin/                         
│   ├── add_item.php              
│   ├── add_user.php              
│   ├── approve_action.php        
│   ├── approve_item.php         
│   ├── archive_action.php        
│   ├── archive_user.php          
│   ├── dashboard.php           
│   ├── manage_user.php           
│   ├── pending_items.php         
│   ├── reset_user_password.php    
│   ├── restore.php               
│   ├── update_item_process.php   
│   ├── user_action.php            
│   └── view_item.php            
│
├── config/
│   └── database.php      
│
├── functions/
│   └── auth.php                 
│
├── superadmin/                   
│   ├── add_admin.php             
│   ├── archive_admin.php       
│   ├── dashboard.php            
│   ├── manage_admin.php          
│   └── reset_admin_password.php   
│
├── uploads/                      
│
├── user/                          
│   ├── add_item.php              
│   ├── approved_items.php       
│   ├── dashboard.php            
│   ├── pending_items.php         
│   ├── rejected_items.php  
│   └── view_item.php          
│
├── dashboard.php               
├── login.php                     
└── logout.php                 
```
## 👥 Credits

- **Developers:** Garis, Benedict / Mabalay, Recelyn / Manzon, Gemma Rose
- **Project Type:** Final Requirement / Inventory Management System
