# Running a Complete Project ZIP File Using XAMPP Locally

This guide explains how to set up and run a PHP-based project (provided as a ZIP file) on your local machine using XAMPP. XAMPP is a free, open-source package that includes Apache, MySQL, PHP, and Perl, making it perfect for testing and developing web applications locally.

---

## Prerequisites

Before you begin, ensure you have the following:

- **XAMPP Installed**: Download and install XAMPP from Apache Friends. [Download XAMPP](https://www.apachefriends.org/download.html)
- **Project ZIP File**: Have the project ZIP file ready on your computer.
- **Text Editor**: Use a code editor like VSCode, Sublime Text, or Notepad++ to view or edit files if needed.
- **Web Browser**: Any modern browser (e.g., Chrome, Firefox) to view the application.

---

## Step-by-Step Guide

### 1. Start XAMPP Services

- Open the **XAMPP Control Panel**.
- Start the **Apache** and **MySQL** services by clicking their respective **Start** buttons.
  - **Apache**: This runs your web server.
  - **MySQL**: This runs your database server.
- Confirm both services are running (their status should turn green).

### 2. Extract the Project ZIP File

- Find the project ZIP file on your computer.
- Right-click and select **Extract All...** (or use a tool like 7-Zip or WinRAR).
- Extract it into the `htdocs` folder of your XAMPP installation:
  - Default location: `C:\xampp\htdocs\`.
  - Example: If the project is named `my_project`, extract it to `C:\xampp\htdocs\my_project`.

### 3. Set Up the Database

- **Access phpMyAdmin**:
  - Open your browser and go to `http://localhost/phpmyadmin/`.
- **Create a New Database**:
  - Click **Databases** in the top menu.
  - Enter a name (e.g., `shop_db_nf3`) and click **Create**.
- **Import SQL File** (if included):
  - The project has an SQL file (e.g., `shop_db_nf3.sql` or `shop_db_nf1.sql` ):
    - Select your new database from the left sidebar.
    - Click the **Import** tab.
    - Click **Choose File**, select the SQL file, and click **Go** to import it.

### 4. Configure Database Connection

- Find the database connection file (e.g., `connect.php` or `db.php`) in the `components` folder inside the project folder.
- Open it in a text editor.
- Update the credentials to match your local setup:
  - Default XAMPP settings:
    - **Host**: `localhost`
    - **Username**: `root`
    - **Password**: (leave blank)
    - **Database Name**: Ensure it is the name you created (e.g., `shop_db_nf3`)
- Example:
    Lets say you named the database in XAMPP `dbname=shop_db_nf3`

```php
$db_name = 'mysql:host=localhost;dbname=shop_db_1nf';
$user_name = 'root';
$user_password = '';  
```
Change this to:

```php
$db_name = 'mysql:host=localhost;dbname=shop_db_nf3';
$user_name = 'root';
$user_password = '';  
```

### 6. Access the Project in Your Browser

- Open your browser.
- Go to `http://localhost/my_project/` (replace `my_project` with your project’s folder name e.g., `Normalform3`).
- Navigate through the application using its links or menus.

### 7. Troubleshooting Common Issues

- **Database Connection Errors**:
  - Verify the database name, username, and password in the connection file.
  - Ensure MySQL is running in XAMPP.
- **Missing Tables**:
  - If you see "Table doesn’t exist," confirm the SQL file was imported correctly.
- **404 Errors**:
  - Check that the project files are in `htdocs` and the URL matches the folder name.
- **Permission Issues**:
  - Adjust folder permissions in `htdocs` to allow read/write access if needed.
- **Port Not Free**:
  - Press `windows + R` and enter `services.msc`
  - Search `MySQL`, `MySQL80`, etc.
  - Stop the service and try to launch the mysql service from XAMPP again.



## Conclusion

Following these steps, you should have the project running locally with XAMPP. This setup lets you test and develop the application before deploying it live. For further help, refer to the troubleshooting tips or the project’s documentation.
