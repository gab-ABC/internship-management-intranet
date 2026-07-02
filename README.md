# Internship Management Intranet

A light, easy to understand, web tools that make the internship management way easier for your school. 

## 🌟 Highlights

* **Role-Based Access Control:** Secure, customized dashboards for Students, Professors, and Admins.
* **Automated Workflow:**        End-to-end validation pipeline from offer submission to final evaluation.
* **Smart Filtering:**           Quick-search system to filter internships by year, name, and company.
* **Document Generation:**       Instant PDF creation for internship agreements and company profiles.

## ℹ️ Overview

This tool features a lightweight, intuitive interface allowing users to easily manage and edit both internship records and company profiles.

> [!TIP]
> Everything can be modified, the code is open source and easily accessible
> If the feature is not to your liking be sure to check the [Code structure](#⚙️-Code-Structure) in the Readme.

They are **3 main roles** to the website:
* **Students:** Can only access in *read-only* the company and the interships
* **Teacher:** They can create companies or interships and access everything except creating users
* **Admin:** They do what they want

The application includes **7 main navigation links**, each leading to a dedicated page:
* **Home Page:** A central dashboard displaying key statistics and analytics.
* **Internship Page:** An advanced search and directory area showcasing all available internships.
* **Company Page:** A dedicated directory for managing and viewing partner companies (similar to the Internship Page).
* **Teacher Page:** A management area to view faculty members and assign teachers to specific students.
* **User Page:** An administrative portal to create new users and manage their access roles.

You can direcly in the website create, modify and delete : **internships**, **companies**, **teachers**
If you are not a **Student** you are allowed and able to access your account info and change your password.

## 💡 Requirements

Like most projects, this application has a few system requirements necessary to run locally or in production.

### 1. Web Server Installation

We will set up a standard **LAMP** (Linux, Apache, MariaDB, PHP) stack:

```bash
# Debian, Ubuntu
sudo apt install apache2 php libapache2-mod-php mariadb-server php-mysql
sudo apt install php-curl php-gd php-intl php-json php-mbstring php-xml php-zip
```
```bash
# Arch Linux
sudo pacman -Syu apache php php-apache mariadb
```

> [!NOTE]
> While these instructions focus on Linux environments, the application can also be run on Windows and macOS by installing the corresponding PHP extensions and server environments (e.g., via XAMPP, MAMP, or Docker).

### 2. Database Management Tool

For database administration, tools like PhpMyAdmin or Adminer (a lightweight alternative) are highly recommended.

```bash
# Debian, Ubuntu
sudo apt install phpmyadmin
```
```bash
# Arch Linux
sudo pacman -S phpmyadmin

# PHP Configuration
sudo nvim /etc/php/php.ini
# Ensure the following extensions are enabled:
# extension=mysqli
# extension=iconv
```
Once installed and configured, you can access the database management interface by navigating to: http://localhost/phpmyadmin

## ⚙️ Code Structure

> [!NOTE]
>Built exclusively with vanilla PHP, the application features a custom routing system developed entirely from scratch. 

The code snippet below illustrates how routes are registered and made accessible to users:

```php
// index.php
$router->add('login', 'AuthController', 'login');
$router->add('home' , 'HomeController', 'index', ['admin', 'prof', 'eleve']);
```

Calling the `add()` method registers a new route within the application. If a user attempts to access a page or URI that has not been explicitly declared in the Router, the system will automatically return a **404 Not Found** error.

The `add()` method accepts up to four parameters:
* **`$name` (string):** The URL path/slug displayed in the browser's address bar.
* **`$controller` (string):** The specific controller class responsible for handling the request.
* **`$action` (string):** The method inside the controller that will be executed.
* **`$roles` (array, optional):** An array defining which user roles (e.g., `['admin', 'prof']`) are authorized to access this route. If omitted, the route is public.

## 📝 Author

* COLLIAU G.

## 🔓 License

[MIT](https://choosealicense.com/licenses/mit/)
