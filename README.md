# OBE Management System

A comprehensive web-based Outcome-Based Education (OBE) management system designed for Tezpur University to automate and streamline educational assessment processes.

## Features

- **Course Outcome Management**: Define and track course learning outcomes
- **Assessment Tracking**: Monitor student assessments and performance
- **Bloom's Taxonomy Analysis**: Analyze questions based on Bloom's taxonomy levels
- **Question Paper Authoring**: Create and manage question papers with CO mapping
- **Course File Management**: Upload and manage course-related documents
- **Role-based Access Control**: Separate dashboards for Admin, Faculty, and Students
- **CO-PO Mapping**: Map course outcomes to program outcomes and PSOs
- **Attainment Calculation**: Automated calculation of course outcome attainment
- **Reporting System**: Generate detailed reports on student performance and attainment

## Technologies Used

- **Backend**: PHP 8.1+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Authentication**: JWT (JSON Web Tokens)
- **Icons**: Font Awesome

## Installation

### Prerequisites

- XAMPP/WAMP or any PHP development environment
- MySQL Server
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Clone or Download the Project**
   ```
   Place the 'obe-php' folder in your web server's document root (e.g., htdocs for XAMPP)
   ```

2. **Database Setup**
   - Create a new MySQL database named `obe_db`
   - Import the `obe_db (1).sql` file located in the project root
   - Update database credentials in `includes/config.php` if necessary

3. **Configuration**
   - Open `includes/config.php`
   - Update the following constants if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'obe_db');
     define('SITE_URL', 'http://localhost/obe-php');
     ```

4. **File Permissions**
   - Ensure the `uploads/` directory is writable by the web server
   - Set appropriate permissions for file uploads

5. **Access the Application**
   - Open your browser and navigate to: `http://localhost/obe-php`
   - Default login credentials:
     - **Admin**: username: `admin`, password: `admin123`
     - **Faculty**: username: `bnath`, password: `teacher123`
     - **Student**: username: `CSB23201`, password: `student123`

## Project Structure

```
obe-php/
├── admin/              # Admin-specific pages
├── assets/             # CSS, JS, and other assets
├── faculty/            # Faculty dashboard and management pages
├── includes/           # Core PHP files (config, auth, functions, etc.)
├── student/            # Student dashboard and pages
├── uploads/            # File upload directory
├── index.php           # Login page
├── dashboard.php       # Main dashboard
├── profile.php         # User profile page
├── forgot-password.php # Password reset functionality
├── reset-password.php  # Password reset form
├── logout.php          # Logout functionality
├── obe_db (1).sql      # Database schema and sample data
└── README.md           # This file
```

## User Roles and Permissions

### Admin
- Manage programs, courses, and users
- View system-wide reports
- Configure program outcomes and PSOs

### Faculty
- Manage assigned courses
- Create assessments and question papers
- Record student marks
- View course-specific reports
- Upload course files

### Student
- View enrolled courses
- Check assessment marks
- Access course materials
- View personal performance reports

## Database Schema

The system uses the following main tables:
- `users` - User accounts and roles
- `programs` - Academic programs
- `courses` - Course information
- `course_outcomes` - Learning outcomes for courses
- `assessments` - Assessment details
- `student_marks` - Student performance data
- `co_po_mapping` - Outcome mapping relationships
- `question_papers` - Question paper templates

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please contact the development team at Tezpur University.

## Acknowledgments

- Developed for Tezpur University
- Built with Bootstrap for responsive design
- Uses JWT for secure authentication
- Implements Bloom's Taxonomy for educational assessment
