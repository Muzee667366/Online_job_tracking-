# Online Job Tracking System

A professional PHP/MySQL web application for recruitment automation.

## 🛠️ Installation Requirements
1. **XAMPP / WAMP / MAMP** (Local Server)
2. **PHP 7.4 or higher**
3. **MySQL Database**

## 🚀 Setup Instructions
1. **Database:** Open phpMyAdmin and create a database named `online_job_tracking`. Import the file `sql/schema.sql`.
2. **Folders:** Copy the project folder into your `htdocs` directory.
3. **Permissions:** Ensure the `uploads/resumes/` folder has write permissions.
4. **Config:** Update `config/db_connect.php` with your local database username and password.
5. **Access:** Navigate to `http://localhost/online_job_tracking/` in your browser.

## 🔐 Credentials for Testing
- **Manager:** Create a user in `tbl_users` with `Role_ID = 1`.
- **Employer:** Create a user with `Role_ID = 2`.
- **Job Seeker:** Register via the frontend (assigns `Role_ID = 3` automatically).