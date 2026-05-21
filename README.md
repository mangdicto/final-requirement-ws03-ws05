Narito ang **na-update na README** na kasama ang lahat ng bagong features na idinagdag natin:

---

# 👕 Clothing Inventory System

A secure, role-based clothing inventory management system built with PHP and MySQL. This system provides a comprehensive solution for managing clothing inventory, user accounts, and item approvals across three distinct user roles.

## ✨ Key Features

### 👥 User Roles & Permissions

| Role | Capabilities |
|------|--------------|
| **Super Admin** | Full system oversight, manages admin accounts, resets admin passwords, archives/restores admins |
| **Admin** | Manages inventory items, approves user-submitted items, manages regular user accounts, archives/restores items and users |
| **User** | Views inventory, submits items for admin approval, tracks pending/approved/rejected items, manages own profile |

### 🔐 Security Features

- **SQL Injection Prevention:** MySQLi Prepared Statements for all database queries
- **XSS Protection:** Output escaping across all views
- **Password Security:** `bcrypt` hashing for all user credentials
- **CSRF Protection:** Secure tokens on all forms and action links to prevent cross-site request forgery
- **Rate Limiting:** 5 failed login attempts per 15 minutes to prevent brute force attacks
- **Password Strength Validation:** Real-time password strength indicator (Weak/Medium/Strong) with requirements checklist
- **Remember Me Functionality:** Secure database-backed token system for persistent login sessions using selector-validator pattern with automatic token rotation
- **Session Timeout:** Automatic logout after 30 minutes of inactivity
- **Action Approval Workflow:** Admin approval required for user-submitted items
- **Archive System:** Soft-delete functionality for items and users

### 🛠️ Admin Management

- Add, archive, and restore admin accounts
- Reset admin passwords
- View all pending user submissions
- Manage user accounts with archive/restore functionality

### 🧑‍💻 User Features

- **User Registration:** Self-registration with password strength validation and auto-login
- **Profile Management:** Update personal information and change password
- Submit new clothing items for admin approval
- Track submission status (pending, approved, rejected)
- View approved items in inventory with search and filter capabilities
- Secure login with optional "Remember Me" persistence

### 🔍 Search & Filter Features

- Search items by name, category, or color
- Filter items by category
- Real-time filtering for approved items view

### ⏰ Session Management

- 30-minute session timeout for security
- Automatic session cleanup on logout
- Remember Me token rotation for enhanced security

## 📦 Tech Stack

| Component | Technology |
|-----------|------------|
| **Frontend** | HTML5, CSS3 (Modern responsive design with Font Awesome icons) |
| **Backend** | PHP (Procedural with modular functions) |
| **Database** | MySQL |
| **Authentication** | Custom session-based with Remember Me cookie persistence |
| **Security** | CSRF tokens, Rate limiting, Password strength validation, bcrypt hashing |
| **Server Requirements** | XAMPP / WAMP / LAMP with PHP 7.4+ |

## 📁 Project Structure

```
CLOTHING_STORE/
│
├── admin/                         # Admin portal (admin only)
│   ├── add_item.php              # Add new inventory item
│   ├── add_user.php              # Add new user account
│   ├── approve_action.php        # Process item approval/rejection
│   ├── approve_item.php          # View pending items for approval
│   ├── archive_action.php        # Archive/restore item processing
│   ├── archive_user.php          # Archive user accounts
│   ├── dashboard.php             # Admin dashboard
│   ├── manage_user.php           # Manage registered users
│   ├── pending_items.php         # View items pending approval
│   ├── reset_user_password.php   # Reset user passwords
│   ├── restore.php               # Restore archived items/users
│   ├── update_item_process.php   # Update item details
│   ├── user_action.php           # User management actions
│   └── view_item.php             # View individual item details
│
├── config/
│   └── database.php              # Database connection configuration
│
├── functions/                    # Core functions library
│   ├── auth.php                  # Authentication, session handling, timeout
│   ├── csrf.php                  # CSRF token generation & validation
│   ├── password_strength.php     # Password validation & strength meter
│   └── rate_limit.php            # Login attempt rate limiting
│
├── superadmin/                   # Super admin portal
│   ├── add_admin.php             # Add new admin account
│   ├── archive_admin.php         # Archive admin accounts
│   ├── dashboard.php             # Super admin dashboard
│   ├── manage_admin.php          # Manage existing admins
│   └── reset_admin_password.php  # Reset admin passwords
│
├── uploads/                      # Uploaded item images storage
│
├── user/                         # Regular user portal
│   ├── add_item.php              # Submit new item for approval
│   ├── approved_items.php        # View approved items (with search/filter)
│   ├── dashboard.php             # User dashboard
│   ├── pending_items.php         # View pending submissions
│   ├── profile.php               # User profile management
│   ├── rejected_items.php        # View rejected submissions
│   └── view_item.php             # View item details
│
├── dashboard.php                 # Role-based landing page
├── login.php                     # User authentication with rate limiting
├── logout.php                    # Session termination & cookie cleanup
└── register.php                  # User self-registration with password strength
```
