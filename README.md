# Student File and Script Hosting Platform

This project is a simple web application built with PHP, PostgreSQL, and Apache, designed to allow students to upload files, including PHP scripts, into individual, isolated directories. A key focus of this project was implementing a secure method for executing these uploaded PHP scripts while preventing students from accessing or manipulating files outside of their assigned directory.

It demonstrates basic user authentication, file uploads with size limits, and dynamic configuration of PHP's `open_basedir` directive to enhance security for arbitrary script execution.

## Features

* **User Authentication:** Secure registration and login system using a password.
* **Individual Student Directories:** Each registered student is assigned a unique directory for their files.
* **File Uploads:** Students can upload files (up to a configurable size limit).
* **Storage Quota:** Tracks and enforces a maximum storage limit per student.
* **File Listing:** Students can view a list of files they have uploaded.
* **Secure Script Execution:** Allows execution of uploaded PHP scripts within the student's own directory using `open_basedir` isolation.
* **PostgreSQL Database:** Uses PDO for database interactions.

## Security Highlight: Script Isolation with `open_basedir`

A core challenge in allowing users to upload and execute arbitrary code (like PHP scripts) is preventing them from accessing sensitive files or directories on the server, or interfering with other users' files.

This project addresses this in the `execute_student_script.php` file by:

1.  **Dynamic `open_basedir`:** Just before including the student's script, PHP's `open_basedir` configuration is dynamically set using `ini_set()`. This restricts *all* file system operations (like `fopen`, `file_get_contents`, `include`, `require`, etc.) performed by the included script *only* to the directories specified in the `open_basedir` value.
2.  **Allowed Paths:** The `open_basedir` is specifically set to include:
    * The student's dedicated upload directory.
    * The PHP session save path.
    * The system temporary directory.
    * This prevents the student script from accessing parent directories (`../`), other student directories, or sensitive server files (like `/etc/passwd` or application configuration files).
3.  **Changing Directory (`chdir`):** The script also changes the current working directory (`chdir`) to the student's upload directory before including the script. This ensures that relative paths used within the student's script resolve correctly within their own directory.

## Prerequisites

* PHP@8.3 with `pgsql` and `pdo_pgsql` extensions enabled.
* PostgreSQL Database Server.
* Apache Web Server (or Nginx) with PHP support using mod_php.
* Make sure to cinfigure your apache and add the following directives:
RewriteEngine On
RewriteRule ^([a-zA-Z0-9_-]+)/(.+\.php)$ /execute_student_script.php?slug=$1&script=$2 [L,QSA]

## Setup

1.  **Clone the Repository:**
    ```bash
    git clone <repository_url>
    cd student_site files_ # Or whatever you name the cloned directory
    ```

2.  **Database Setup:**
    * Create a PostgreSQL database (e.g., `student_site_db`) and a user (e.g., `student_site_admin`) with permissions on the database.
    * Import the database schema located at the root of the cloned directory:
        ```bash
        psql -U student_site_admin -d student_site_db -f student_site_db.sql
        ```
        *(You may need to adjust the command based on your PostgreSQL setup)*

3.  **Configure `config.php`:**
    * Edit the `config.php` file located at the root of the cloned directory.
    * Update the database credentials (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`).
    * **Crucially**, define `STUDENT_UPLOADS_BASE_PATH` to an absolute path *outside* your web server's document root (e.g., `/var/www/student_files` or `/home/myuser/student_data`). **Do not place this inside your webroot.**
    * Example (adjust the path):
        ```php
        define('STUDENT_UPLOADS_BASE_PATH', '/var/www/student_files'); // Or /home/youruser/student_files etc.
        define('MAX_STORAGE_BYTES', 5 * 1024 * 1024); // 5MB
        // ... other configs
        ```

4.  **Create Upload Directory:**
    * Create the directory specified in `STUDENT_UPLOADS_BASE_PATH` on your server.
    * Ensure your web server's PHP process user has read, write, and execute permissions on this directory. For example, if your web server runs as `www-data`:
        ```bash
        sudo mkdir /var/www/student_files
        sudo chown www-data:www-data /var/www/student_files
        # Or more permissive, depending on your setup needs, but be cautious:
        # sudo chmod 775 /var/www/student_files
        ```

5.  **Web Server Configuration (Apache Example):**
    * You need to configure your web server to:
        * Serve the PHP application files (`index.php`, `login.php`, etc.) from the root of your cloned directory (`student_site files_`).
        * Handle requests to `/student_files/{slug}/{script.php}` by routing them to your `execute_student_script.php` script.
    * Here's a recommended Apache configuration using `RewriteRule` to handle script execution securely. Place this within your VirtualHost or main server config, adjusting paths:

    ```apache
    # Assuming your site root points to the 'student_site files_' directory
    DocumentRoot "/path/to/your/student_site files_" # Adjust this path

    <Directory "/path/to/your/student_site files_"> # Adjust this path
        Options Indexes FollowSymLinks
        AllowOverride All # Allow .htaccess if needed
        Require all granted
    </Directory>

    RewriteEngine On

    # Serve static files directly from the upload directory for non-PHP extensions
    # Add other safe extensions as needed (txt, jpg, png, css, js, pdf, html, etc.)
    RewriteRule ^student_files/([^/]+)/(.*?\.(txt|jpg|jpeg|png|gif|css|js|pdf|html))$ "/path/to/your/STUDENT_UPLOADS_BASE_PATH/$1/$2" [L]

    # Route requests for .php files to the execution script
    # Ensure the path to execute_student_script.php is correct relative to your DocumentRoot
    RewriteRule ^student_files/([^/]+)/(.*?\.(php))$ /execute_student_script.php?slug=$1&script=$2 [L,QSA]

    # Prevent direct access to execute_student_script.php without parameters (optional but good)
    RewriteCond %{QUERY_STRING} !slug=.*
    RewriteRule ^execute_student_script\.php$ - [F] # Forbidden

    # Optional: If using mod_php
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    ```
    *Replace `/path/to/your/student_site files_` and `/path/to/your/STUDENT_UPLOADS_BASE_PATH` with your actual paths.* Add this configuration snippet (or a more complete VirtualHost config) to an `apache-config-example.conf` file in your repository's root.

6.  **Link CSS:** Ensure your `templates/header.php` includes a link to `css/style.css`. Add this line inside the `<head>` section:
    ```html
    <link rel="stylesheet" href="/css/style.css">
    ```

## Usage

1.  Navigate to your site's base URL (e.g., `http://localhost/` or `http://your-domain.com/`). You will be redirected to the login page.
2.  **Register:** Create a new student account via `register.php`. The username will be used to create your unique directory slug.
3.  **Login:** Log in with your new account.
4.  **Dashboard:** You will see your assigned directory slug, storage usage, and a list of your uploaded files.
5.  **Upload Files:** Use the form on the dashboard to upload files.
6.  **View/Execute Files:** Click the links in the "Your Files" list.
    * Non-PHP files will be served directly (assuming the Apache RewriteRule is set up).
    * PHP files will trigger the `execute_student_script.php` which runs the script securely within the defined `open_basedir`.

The URL structure for viewing/executing files will be `/student_files/{your-directory-slug}/{your-file-name}`.

## Project Structure (As Cloned)
student_site files_/ # Or your chosen directory name
├── config.php
├── css/
│   └── style.css
├── dashboard.php
├── db.php
├── execute_student_script.php
├── index.php
├── login.php
├── logout.php
├── register.php
├── student_site_db.sql # Database schema
├── templates/
│   ├── footer.php
│   └── header.php
├── upload.php
└── README.md                  # (This file)
