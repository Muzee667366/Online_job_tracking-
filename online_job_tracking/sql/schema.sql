-- File: sql/schema.sql
-- Online Job Tracking System Database Schema

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS online_job_tracking;
USE online_job_tracking;

-- =====================================================
-- Table: tbl_roles
-- Description: Stores user roles for RBAC
-- =====================================================
CREATE TABLE tbl_roles (
    Role_ID INT PRIMARY KEY AUTO_INCREMENT,
    Role_Name VARCHAR(50) NOT NULL,
    Description VARCHAR(255),
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table: tbl_users
-- Description: Stores all system users
-- =====================================================
CREATE TABLE tbl_users (
    User_ID INT PRIMARY KEY AUTO_INCREMENT,
    Full_Name VARCHAR(255) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Phone_Number VARCHAR(20),
    Address TEXT,
    Profile_Picture VARCHAR(255),
    Role_ID INT,
    Is_Active BOOLEAN DEFAULT TRUE,
    Last_Login TIMESTAMP NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (Role_ID) REFERENCES tbl_roles(Role_ID),
    INDEX idx_email (Email),
    INDEX idx_role (Role_ID)
);

-- =====================================================
-- Table: tbl_employer_profiles
-- Description: Extended information for employers
-- =====================================================
CREATE TABLE tbl_employer_profiles (
    Employer_ID INT PRIMARY KEY,
    Company_Name VARCHAR(255) NOT NULL,
    Company_Logo VARCHAR(255),
    Company_Website VARCHAR(255),
    Company_Description TEXT,
    Industry VARCHAR(100),
    Company_Size VARCHAR(50),
    Founded_Year YEAR,
    Contact_Person VARCHAR(255),
    Contact_Phone VARCHAR(20),
    Contact_Email VARCHAR(100),
    Verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (Employer_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE
);

-- =====================================================
-- Table: tbl_job_seeker_profiles
-- Description: Extended information for job seekers
-- =====================================================
CREATE TABLE tbl_job_seeker_profiles (
    Seeker_ID INT PRIMARY KEY,
    Date_of_Birth DATE,
    Gender ENUM('Male', 'Female', 'Other'),
    Education_Level VARCHAR(100),
    Field_of_Study VARCHAR(255),
    Years_of_Experience INT DEFAULT 0,
    Current_Job_Title VARCHAR(255),
    Current_Company VARCHAR(255),
    Skills TEXT,
    Languages TEXT,
    Certifications TEXT,
    FOREIGN KEY (Seeker_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE
);

-- =====================================================
-- Table: tbl_jobs
-- Description: Stores job postings from employers
-- =====================================================
CREATE TABLE tbl_jobs (
    Job_ID INT PRIMARY KEY AUTO_INCREMENT,
    Job_Title VARCHAR(255) NOT NULL,
    Job_Description TEXT NOT NULL,
    Requirements TEXT,
    Responsibilities TEXT,
    Job_Category VARCHAR(100),
    Job_Type ENUM('Full Time', 'Part Time', 'Contract', 'Internship', 'Remote') DEFAULT 'Full Time',
    Location VARCHAR(255),
    Salary_Min DECIMAL(10,2),
    Salary_Max DECIMAL(10,2),
    Salary_Currency VARCHAR(10) DEFAULT 'ETB',
    Experience_Level VARCHAR(50),
    Education_Required VARCHAR(100),
    Skills_Required TEXT,
    Benefits TEXT,
    Application_Deadline DATE,
    Employer_ID INT,
    Status ENUM('Active', 'Closed', 'Draft', 'Expired') DEFAULT 'Active',
    Views_Count INT DEFAULT 0,
    Applications_Count INT DEFAULT 0,
    Posted_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (Employer_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    INDEX idx_employer (Employer_ID),
    INDEX idx_category (Job_Category),
    INDEX idx_status (Status),
    INDEX idx_posted_date (Posted_Date)
);

-- =====================================================
-- Table: tbl_applications
-- Description: Stores job applications from job seekers
-- =====================================================
CREATE TABLE tbl_applications (
    Application_ID INT PRIMARY KEY AUTO_INCREMENT,
    Job_ID INT NOT NULL,
    User_ID INT NOT NULL,
    Application_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Cover_Letter TEXT,
    Resume_Path VARCHAR(255),
    Expected_Salary DECIMAL(10,2),
    Available_Start_Date DATE,
    Application_Status ENUM('Pending', 'Reviewed', 'Shortlisted', 'Accepted', 'Rejected', 'Withdrawn') DEFAULT 'Pending',
    Employer_Notes TEXT,
    Interview_Date DATETIME,
    Interview_Location VARCHAR(255),
    Interview_Notes TEXT,
    Reviewed_By INT,
    Reviewed_Date TIMESTAMP NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (Job_ID) REFERENCES tbl_jobs(Job_ID) ON DELETE CASCADE,
    FOREIGN KEY (User_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Reviewed_By) REFERENCES tbl_users(User_ID) ON DELETE SET NULL,
    UNIQUE KEY unique_application (Job_ID, User_ID),
    INDEX idx_job (Job_ID),
    INDEX idx_user (User_ID),
    INDEX idx_status (Application_Status),
    INDEX idx_date (Application_Date)
);

-- =====================================================
-- Table: tbl_saved_jobs
-- Description: Allows job seekers to save jobs for later
-- =====================================================
CREATE TABLE tbl_saved_jobs (
    Saved_ID INT PRIMARY KEY AUTO_INCREMENT,
    User_ID INT NOT NULL,
    Job_ID INT NOT NULL,
    Saved_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Notes VARCHAR(255),
    FOREIGN KEY (User_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Job_ID) REFERENCES tbl_jobs(Job_ID) ON DELETE CASCADE,
    UNIQUE KEY unique_saved (User_ID, Job_ID),
    INDEX idx_user (User_ID)
);

-- =====================================================
-- Table: tbl_job_alerts
-- Description: Job seekers can set up job alerts
-- =====================================================
CREATE TABLE tbl_job_alerts (
    Alert_ID INT PRIMARY KEY AUTO_INCREMENT,
    User_ID INT NOT NULL,
    Alert_Name VARCHAR(255),
    Keywords VARCHAR(255),
    Category VARCHAR(100),
    Job_Type VARCHAR(50),
    Location VARCHAR(255),
    Salary_Min DECIMAL(10,2),
    Frequency ENUM('Daily', 'Weekly', 'Instant') DEFAULT 'Daily',
    Is_Active BOOLEAN DEFAULT TRUE,
    Created_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Last_Sent TIMESTAMP NULL,
    FOREIGN KEY (User_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    INDEX idx_user (User_ID)
);

-- =====================================================
-- Table: tbl_messages
-- Description: Messaging between employers and job seekers
-- =====================================================
CREATE TABLE tbl_messages (
    Message_ID INT PRIMARY KEY AUTO_INCREMENT,
    Sender_ID INT NOT NULL,
    Receiver_ID INT NOT NULL,
    Subject VARCHAR(255),
    Message TEXT NOT NULL,
    Is_Read BOOLEAN DEFAULT FALSE,
    Sent_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Read_Date TIMESTAMP NULL,
    Parent_Message_ID INT,
    FOREIGN KEY (Sender_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Receiver_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Parent_Message_ID) REFERENCES tbl_messages(Message_ID) ON DELETE SET NULL,
    INDEX idx_sender (Sender_ID),
    INDEX idx_receiver (Receiver_ID),
    INDEX idx_read (Is_Read)
);

-- =====================================================
-- Table: tbl_notifications
-- Description: System notifications for users
-- =====================================================
CREATE TABLE tbl_notifications (
    Notification_ID INT PRIMARY KEY AUTO_INCREMENT,
    User_ID INT NOT NULL,
    Notification_Type ENUM('Application', 'Message', 'Job', 'System') DEFAULT 'System',
    Title VARCHAR(255) NOT NULL,
    Message TEXT,
    Link VARCHAR(255),
    Is_Read BOOLEAN DEFAULT FALSE,
    Created_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Read_Date TIMESTAMP NULL,
    FOREIGN KEY (User_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    INDEX idx_user (User_ID),
    INDEX idx_read (Is_Read)
);

-- =====================================================
-- Table: tbl_reviews
-- Description: Reviews and ratings for employers
-- =====================================================
CREATE TABLE tbl_reviews (
    Review_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employer_ID INT NOT NULL,
    Reviewer_ID INT NOT NULL,
    Rating INT CHECK (Rating >= 1 AND Rating <= 5),
    Review_Text TEXT,
    Review_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Is_Verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (Employer_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Reviewer_ID) REFERENCES tbl_users(User_ID) ON DELETE CASCADE,
    INDEX idx_employer (Employer_ID)
);

-- =====================================================
-- Table: tbl_reports
-- Description: Generated reports for managers
-- =====================================================
CREATE TABLE tbl_reports (
    Report_ID INT PRIMARY KEY AUTO_INCREMENT,
    Report_Name VARCHAR(255) NOT NULL,
    Report_Type VARCHAR(100),
    Generated_By INT,
    Generated_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Report_Data LONGTEXT,
    File_Path VARCHAR(255),
    Parameters TEXT,
    FOREIGN KEY (Generated_By) REFERENCES tbl_users(User_ID) ON DELETE SET NULL
);

-- =====================================================
-- Table: tbl_audit_logs
-- Description: Track all important system activities
-- =====================================================
CREATE TABLE tbl_audit_logs (
    Log_ID INT PRIMARY KEY AUTO_INCREMENT,
    User_ID INT,
    Action VARCHAR(100) NOT NULL,
    Table_Name VARCHAR(100),
    Record_ID INT,
    Old_Value TEXT,
    New_Value TEXT,
    IP_Address VARCHAR(45),
    User_Agent TEXT,
    Action_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_ID) REFERENCES tbl_users(User_ID) ON DELETE SET NULL,
    INDEX idx_user (User_ID),
    INDEX idx_date (Action_Date),
    INDEX idx_action (Action)
);

-- =====================================================
-- Insert Default Data
-- =====================================================

-- Insert Roles
INSERT INTO tbl_roles (Role_Name, Description) VALUES 
('Manager', 'System administrator with full access'),
('Employer', 'Company representative who posts jobs'),
('Job Seeker', 'Individual looking for jobs');

-- Insert Default Manager (password: Admin@123 - you should change this)
INSERT INTO tbl_users (Full_Name, Email, Password, Role_ID, Is_Active) VALUES 
('System Administrator', 'admin@jobtracker.com', '$2y$10$YourHashedPasswordHere', 1, TRUE);

-- Insert Sample Employer (password: Employer@123)
INSERT INTO tbl_users (Full_Name, Email, Password, Role_ID, Is_Active) VALUES 
('ABC Company', 'employer@jobtracker.com', '$2y$10$YourHashedPasswordHere', 2, TRUE);

-- Insert Sample Job Seeker (password: Seeker@123)
INSERT INTO tbl_users (Full_Name, Email, Password, Role_ID, Is_Active) VALUES 
('John Doe', 'seeker@jobtracker.com', '$2y$10$YourHashedPasswordHere', 3, TRUE);

-- Insert Sample Employer Profile
INSERT INTO tbl_employer_profiles (Employer_ID, Company_Name, Industry, Company_Size, Verified) VALUES 
(2, 'ABC Company', 'Technology', '50-100', TRUE);

-- Insert Sample Job Seeker Profile
INSERT INTO tbl_job_seeker_profiles (Seeker_ID, Education_Level, Years_of_Experience, Skills) VALUES 
(3, 'Bachelor Degree', 3, 'PHP, MySQL, JavaScript, HTML, CSS');

-- Insert Sample Jobs
INSERT INTO tbl_jobs (Job_Title, Job_Description, Requirements, Job_Category, Job_Type, Location, Salary_Min, Salary_Max, Employer_ID, Status) VALUES 
('Senior PHP Developer', 'We are looking for an experienced PHP developer...', '5+ years PHP experience, MySQL, Laravel', 'Technology', 'Full Time', 'Addis Ababa', 15000, 25000, 2, 'Active'),
('Marketing Manager', 'Lead our marketing team...', '3+ years marketing experience', 'Marketing', 'Full Time', 'Addis Ababa', 12000, 20000, 2, 'Active'),
('Graphic Designer', 'Create amazing designs...', 'Adobe Creative Suite', 'Design', 'Contract', 'Remote', 8000, 15000, 2, 'Active');

-- Insert Sample Applications
INSERT INTO tbl_applications (Job_ID, User_ID, Cover_Letter, Expected_Salary, Application_Status) VALUES 
(1, 3, 'I am very interested in this position...', 18000, 'Pending'),
(2, 3, 'I have experience in marketing...', 15000, 'Reviewed');

-- =====================================================
-- Create Views for Common Queries
-- =====================================================

-- View: vw_job_details
CREATE VIEW vw_job_details AS
SELECT 
    j.*,
    u.Full_Name as Employer_Name,
    u.Email as Employer_Email,
    ep.Company_Name,
    ep.Company_Logo,
    ep.Company_Website,
    (SELECT COUNT(*) FROM tbl_applications WHERE Job_ID = j.Job_ID) as Total_Applications
FROM tbl_jobs j
LEFT JOIN tbl_users u ON j.Employer_ID = u.User_ID
LEFT JOIN tbl_employer_profiles ep ON u.User_ID = ep.Employer_ID
WHERE j.Status = 'Active';

-- View: vw_application_details
CREATE VIEW vw_application_details AS
SELECT 
    a.*,
    j.Job_Title,
    j.Job_Category,
    j.Location as Job_Location,
    j.Salary_Min,
    j.Salary_Max,
    u.Full_Name as Applicant_Name,
    u.Email as Applicant_Email,
    u.Phone_Number,
    jsp.Education_Level,
    jsp.Years_of_Experience,
    jsp.Skills
FROM tbl_applications a
LEFT JOIN tbl_jobs j ON a.Job_ID = j.Job_ID
LEFT JOIN tbl_users u ON a.User_ID = u.User_ID
LEFT JOIN tbl_job_seeker_profiles jsp ON u.User_ID = jsp.Seeker_ID;

-- =====================================================
-- Create Stored Procedures
-- =====================================================

-- Procedure: sp_get_employer_statistics
DELIMITER //
CREATE PROCEDURE sp_get_employer_statistics(IN emp_id INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = emp_id) as total_jobs,
        (SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = emp_id AND Status = 'Active') as active_jobs,
        (SELECT COUNT(*) FROM tbl_applications a 
         INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
         WHERE j.Employer_ID = emp_id) as total_applications,
        (SELECT COUNT(DISTINCT a.User_ID) FROM tbl_applications a 
         INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
         WHERE j.Employer_ID = emp_id) as unique_applicants;
END//
DELIMITER ;

-- Procedure: sp_get_job_seeker_statistics
DELIMITER //
CREATE PROCEDURE sp_get_job_seeker_statistics(IN seeker_id INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM tbl_applications WHERE User_ID = seeker_id) as total_applications,
        (SELECT COUNT(*) FROM tbl_applications WHERE User_ID = seeker_id AND Application_Status = 'Pending') as pending_applications,
        (SELECT COUNT(*) FROM tbl_applications WHERE User_ID = seeker_id AND Application_Status = 'Reviewed') as reviewed_applications,
        (SELECT COUNT(*) FROM tbl_applications WHERE User_ID = seeker_id AND Application_Status = 'Accepted') as accepted_applications,
        (SELECT COUNT(*) FROM tbl_saved_jobs WHERE User_ID = seeker_id) as saved_jobs;
END//
DELIMITER ;

-- =====================================================
-- Create Triggers
-- =====================================================

-- Trigger: Update applications count in jobs table
DELIMITER //
CREATE TRIGGER trg_update_job_applications_count
AFTER INSERT ON tbl_applications
FOR EACH ROW
BEGIN
    UPDATE tbl_jobs 
    SET Applications_Count = Applications_Count + 1 
    WHERE Job_ID = NEW.Job_ID;
END//
DELIMITER ;

-- Trigger: Create notification for new application
DELIMITER //
CREATE TRIGGER trg_application_notification
AFTER INSERT ON tbl_applications
FOR EACH ROW
BEGIN
    DECLARE employer_id INT;
    DECLARE job_title VARCHAR(255);
    
    SELECT j.Employer_ID, j.Job_Title INTO employer_id, job_title
    FROM tbl_jobs j WHERE j.Job_ID = NEW.Job_ID;
    
    INSERT INTO tbl_notifications (User_ID, Notification_Type, Title, Message, Link)
    VALUES (employer_id, 'Application', 'New Application Received', 
            CONCAT('You have received a new application for ', job_title),
            CONCAT('view_applicants.php?id=', NEW.Job_ID));
END//
DELIMITER ;

-- =====================================================
-- Indexes for Performance
-- =====================================================

-- Additional indexes for better query performance
CREATE INDEX idx_jobs_salary ON tbl_jobs(Salary_Min, Salary_Max);
CREATE INDEX idx_jobs_deadline ON tbl_jobs(Application_Deadline);
CREATE INDEX idx_applications_status_date ON tbl_applications(Application_Status, Application_Date);
CREATE INDEX idx_users_email_password ON tbl_users(Email, Password);
CREATE INDEX idx_notifications_user_read ON tbl_notifications(User_ID, Is_Read);

-- =====================================================
-- Sample Queries for Testing
-- =====================================================

/*
-- Get all active jobs with company names
SELECT j.*, ep.Company_Name 
FROM tbl_jobs j
LEFT JOIN tbl_employer_profiles ep ON j.Employer_ID = ep.Employer_ID
WHERE j.Status = 'Active'
ORDER BY j.Posted_Date DESC;

-- Get applications for a specific job
SELECT a.*, u.Full_Name, u.Email 
FROM tbl_applications a
LEFT JOIN tbl_users u ON a.User_ID = u.User_ID
WHERE a.Job_ID = 1
ORDER BY a.Application_Date DESC;

-- Get employer dashboard data
SELECT 
    u.Full_Name,
    ep.Company_Name,
    (SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = u.User_ID) as total_jobs,
    (SELECT COUNT(*) FROM tbl_applications a 
     JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
     WHERE j.Employer_ID = u.User_ID) as total_applications
FROM tbl_users u
LEFT JOIN tbl_employer_profiles ep ON u.User_ID = ep.Employer_ID
WHERE u.Role_ID = 2;
*/

-- Commit all changes
COMMIT;