# TravelNest

### Database Lab Final Project

TravelNest is a PHP and MySQL hotel booking application created for the Database Lab final project. It lets users explore destinations, compare hotels and rooms, make bookings, submit reviews, and view their booking history. An admin panel provides management tools and database reports.

The project demonstrates the database concepts covered in the lab through a working web application, including CRUD operations, joins, aggregate functions, subqueries, set operations, views, filtering, grouping, and report generation.

## Screenshots

Add the following screenshots to the project root beside this README:

![TravelNest home page](hom1.png)

![TravelNest home page](home2.png)

## Features

### User features

- Browse destinations and filter them by name, description, or country.
- Browse hotels by name, country, and price range.
- View hotel rooms, availability, ratings, and applicable discounts.
- Book an available room with check-in, check-out, and guest details.
- View booking history with booking-status summaries.
- Submit hotel reviews and view recent reviews.
- View hotels with an average rating of at least 3.5.
- Register and log in with session-based authentication.

### Admin features

- View dashboard statistics for users, destinations, hotels, bookings, and reviews.
- View revenue, total payments, and average payment values.
- Manage users and update user roles.
- Add, update, and delete destinations.
- Add, update, and delete hotels.
- Add, update, and delete rooms.
- View all bookings and update booking or payment status.
- Delete bookings and related payment records.
- Generate analytical reports and download the generated text report.

## Database Concepts Demonstrated

The application includes practical examples of:

- `SELECT`, `INSERT`, `UPDATE`, and `DELETE` statements.
- `WHERE`, `LIKE`, `IN`, `BETWEEN`, `ORDER BY`, `GROUP BY`, and `HAVING`.
- Aggregate functions including `COUNT`, `SUM`, `AVG`, `MIN`, and `MAX`.
- `INNER JOIN`, `LEFT JOIN`, `RIGHT JOIN`, self join, equi join, non-equi join, natural join, and Cartesian join.
- Simulated full outer joins using `UNION`.
- Subqueries for finding hotels above the average rating.
- Multi-table joins across users, bookings, rooms, hotels, destinations, and payments.
- SQL views, including the `v_hotel_details` view used by the admin dashboard.
- Set membership and exclusion queries using `IN` and `NOT IN`.
- Report output written to a text file, similar to SQL*Plus `SPOOL`.

The dedicated SQL demonstration page is available at `joins_demo.php`, and the reporting page is available through the admin panel at `index.php?page=admin_reports`.

## Database Structure

The main entities used by TravelNest are:

| Table | Purpose |
| --- | --- |
| `users` | Stores user details, login information, and roles. |
| `destinations` | Stores destination names, countries, and descriptions. |
| `hotels` | Stores hotels linked to destinations, including price and rating. |
| `rooms` | Stores room types, prices, availability, and hotel relationships. |
| `bookings` | Stores room reservations, dates, guests, and booking status. |
| `payments` | Stores payment amount, method, and payment status for bookings. |
| `reviews` | Stores hotel ratings and user comments. |
| `discounts` | Stores price ranges and discount percentages used for room pricing. |

Important relationships include:

```text
destinations 1 ---- many hotels
hotels       1 ---- many rooms
users        1 ---- many bookings
rooms        1 ---- many bookings
bookings     1 ---- many payments
users        1 ---- many reviews
hotels       1 ---- many reviews
```

## Application Pages

| Page | Description |
| --- | --- |
| `index.php` | Main router and TravelNest home page. |
| `destinations.php` | Destination search and country filtering. |
| `hotels.php` | Hotel listing, filtering, sorting, and country statistics. |
| `rooms.php` | Room listing, availability, and discount calculation. |
| `book.php` | Room booking form and availability update. |
| `my_bookings.php` | Current user's bookings and status summary. |
| `reviews.php` | Review submission, review listing, and rating analysis. |
| `search.php` | Natural-language travel search using Gemini-generated `SELECT` queries. |
| `joins_demo.php` | Interactive examples of SQL join types. |
| `admin_dashboard.php` | Admin statistics and summary dashboard. |
| `admin_users.php` | User and role management. |
| `admin_destinations.php` | Destination management. |
| `admin_hotels.php` | Hotel management. |
| `admin_rooms.php` | Room management. |
| `admin_bookings.php` | Booking and payment-status management. |
| `admin_reports.php` | SQL reports, analysis, and report generation. |

## Technology Stack

- PHP 7.4+ or PHP 8.x
- MySQL / MariaDB
- PDO for database access
- Bootstrap 5.3
- Tailwind CSS CDN utilities
- Bootstrap Icons
- Gemini API for the optional natural-language search page
- Apache, XAMPP, WAMP, or PHP's built-in development server

## Requirements

- PHP with the `PDO` and `pdo_mysql` extensions enabled.
- MySQL or MariaDB server.
- A browser.
- Optional: a Gemini API key and cURL support for `search.php`.

## Setup and Run

### 1. Clone or download the project

```bash
git clone <your-repository-url>
cd TravelNest---dabtabase-project
```

### 2. Create the database

Create a MySQL database named `travelnest` and load the SQL schema and sample data used for the lab. This repository currently contains the PHP application files but does not include a database dump, so the schema must be created or imported separately.

### 3. Configure the connection

Edit `travelnest/config/db.php` and update these values for your local MySQL installation:

```php
$host = '127.0.0.1';
$port = '3308';
$db   = 'travelnest';
$user = 'root';
$pass = '';
```

The current configuration expects MySQL to run on port `3308`. Change it to `3306` if that is the port used by your installation.

### 4. Start the application

From the repository root, run:

```bash
php -S localhost:8000 -t travelnest
```

Open [http://localhost:8000](http://localhost:8000) in a browser.

With XAMPP or WAMP, place the project in the server's web directory and open:

```text
http://localhost/TravelNest---dabtabase-project/travelnest/
```

## Useful URLs

| URL | Purpose |
| --- | --- |
| `index.php` | Home page |
| `destinations.php` | Browse destinations |
| `hotels.php` | Browse and filter hotels |
| `reviews.php` | Submit and browse reviews |
| `joins_demo.php` | Explore SQL join examples |
| `login.php` | User login |
| `signup.php` | User registration |
| `index.php?page=admin_dashboard` | Admin dashboard |
| `index.php?page=admin_reports` | Admin reports |

## Project Structure

```text
TravelNest---dabtabase-project/
├── README.md
└── travelnest/
	├── config/
	│   └── db.php
	├── index.php
	├── destinations.php
	├── hotels.php
	├── rooms.php
	├── book.php
	├── my_bookings.php
	├── reviews.php
	├── search.php
	├── joins_demo.php
	├── login.php
	├── signup.php
	├── logout.php
	└── admin_*.php
```

## Notes

- The application uses prepared statements for most user-input queries.
- Several pages use demo user ID `1` for the lab workflow; replace this with the authenticated session user's ID when extending the project.
- Keep API keys outside committed source code when enabling the Gemini search feature.
- The generated admin report is written to `travelnest/report_output.txt` and can be downloaded through `download_report.php`.

## Academic Project

This project was developed as the Database Lab final project to apply relational database design and SQL concepts in a practical hotel-booking application.
