# final-requirement-ws03-ws05

Here's a professional README structure for your Clothing Inventory System based on the GitHub reference you provided.

---

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
├── admin/                          # Admin portal (admin only)
│   ├── add_item.php               # Add new inventory item
│   ├── add_user.php               # Add new user account
│   ├── approve_action.php         # Process item approval/rejection
│   ├── approve_item.php           # View pending items for approval
│   ├── archive_action.php         # Archive/restore processing
│   ├── archive_user.php           # Archive user accounts
│   ├── dashboard.php              # Admin dashboard
│   ├── manage_user.php            # Manage registered users
│   ├── pending_items.php          # View items pending approval
│   ├── reset_user_password.php    # Reset user passwords
│   ├── restore.php                # Restore archived items/users
│   ├── update_item_process.php    # Update item details
│   ├── user_action.php            # User management actions
│   └── view_item.php              # View individual item details
│
├── config/
│   └── database.php               # Database connection configuration
│
├── functions/
│   └── auth.php                   # Authentication functions & session handling
│
├── superadmin/                     # Super admin portal
│   ├── add_admin.php              # Add new admin account
│   ├── archive_admin.php          # Archive admin accounts
│   ├── dashboard.php              # Super admin dashboard
│   ├── manage_admin.php           # Manage existing admins
│   └── reset_admin_password.php   # Reset admin passwords
│
├── uploads/                        # Uploaded item images storage
│
├── user/                           # Regular user portal
│   ├── add_item.php               # Submit new item for approval
│   ├── approved_items.php         # View approved items
│   ├── dashboard.php              # User dashboard
│   ├── pending_items.php          # View pending submissions
│   ├── rejected_items.php         # View rejected submissions
│   └── view_item.php              # View item details
│
├── dashboard.php                   # Role-based landing page
├── login.php                       # User authentication with Remember Me
└── logout.php                      # Session termination & cookie cleanup
```
## 👥 Credits

- **Developers:** Garis, Benedict/Mabalay, Recelyn/Manzon, Gemma Rose
- **Project Type:** Final Requirement / Inventory Management System
- **Course:** Web Development / Database Management
