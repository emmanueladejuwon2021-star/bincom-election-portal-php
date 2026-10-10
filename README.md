# Bincom ICT Solutions: PHP & MySQL Technical Assessment

An election results collation and reporting portal for the Delta State election dataset, developed using pure PHP and PDO with MySQL as the primary database.

![Delta State Election Portal Preview](public/img/dashboard-preview.png)

---

## Project Overview

This repository contains the complete implementation for the Bincom ICT Solutions technical assessment. The application provides an interface for inspecting individual polling unit results, computing aggregated local government council totals, and recording new polling unit returns.

---

## Solutions Implemented

### Question 1: Polling Unit Results Explorer (`polling-unit.php`)
- Allows users to select any individual polling unit and view the announced results for all contesting parties.
- Features three-tier cascaded selectors: Local Government Area (LGA) -> Ward -> Polling Unit.
- Includes an instant search bar to find polling units by name, location description, or unique identifier.
- Displays vote counts, percentage shares, visual progress indicators, and an interactive bar chart.
- Retrieves accredited agent and presiding officer details from the `agentname` table.

### Question 2: Local Government Total Collation (`lga-total.php`)
- Computes and displays the summed total vote count of all polling units under any selected Local Government Area.
- Performs real-time aggregation across the `announced_pu_results` and `polling_unit` tables.
- Visualizes party distribution via an interactive doughnut chart.
- Includes a verification table comparing grassroots polling unit sums against declared council results to highlight audit reconciliation.
- Provides one-click CSV export for audit review.

### Question 3: New Polling Unit Data Entry (`new-polling-unit.php`)
- Provides a structured form to register a new polling unit under any ward and enter vote scores for all contesting political parties.
- Dynamic ward loading based on the selected Local Government Area.
- Real-time client-side total vote counter as scores are entered.
- Captures presiding officer and accredited party agent credentials into the `agentname` table.
- Implements atomic PDO transactions (`beginTransaction`, `commit`, `rollBack`) to guarantee referential integrity across `polling_unit`, `announced_pu_results`, and `agentname`.

### Additional Feature: Statewide Collation Dashboard (`index.php`)
- High-level overview of total votes cast, reporting polling units, and leading political parties.
- Complete 25-LGA directory with progress indicators.
- One-click CSV export of statewide results.
- Day and night mode toggle with persistent user preference.

---

## Technical Stack & Architecture

- **Backend**: Native PHP 8.x with PDO (PHP Data Objects). No external frameworks used.
- **Database**: MySQL (`bincom_test`).
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, and Chart.js.
- **Security**: Prepared statements across all SQL operations to prevent SQL injection.
- **Containerization**: Dockerfile with Apache and MySQL for cloud deployment.

---

## Database Configuration

### Default Credentials (`config/db.php`)
- **Host**: `127.0.0.1` (or `localhost`)
- **Port**: `3306`
- **Database**: `bincom_test`
- **Username**: `root`
- **Password**: *(blank)*

These settings align with default configurations in XAMPP, WAMP, and Laragon. Credentials can also be configured via environment variables (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`).

### Importing the Database

You can initialize the database using any of the following methods:

#### Method 1: Automated Script (Recommended)
Run the setup script in your terminal:
```bash
php setup_mysql.php
```
Or open it in your browser once your web server is running:
```
http://localhost/bincom-php/setup_mysql.php
```
This script creates the `bincom_test` database if it does not already exist and imports all tables and election records from `bincom_test.sql`.

#### Method 2: MySQL Command Line
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS bincom_test;"
mysql -u root -p bincom_test < bincom_test.sql
```

#### Method 3: phpMyAdmin
1. Open phpMyAdmin (`http://localhost/phpmyadmin`).
2. Create a new database named `bincom_test`.
3. Select `bincom_test`, navigate to the **Import** tab, choose `bincom_test.sql`, and click **Import**.

---

## How to Run Locally

### Using XAMPP, WAMP, or Laragon
1. Place the `bincom-php` directory into your web root (`htdocs/bincom-php` or `www/bincom-php`).
2. Start the Apache and MySQL services from your control panel.
3. Open your browser and navigate to:
   ```
   http://localhost/bincom-php
   ```

### Using the PHP Built-In Server
1. Open a terminal inside the project directory:
   ```bash
   cd bincom-php
   php -S localhost:8000
   ```
2. Open your browser and navigate to:
   ```
   http://localhost:8000
   ```

---

## Deployment on Render

This project includes a production `Dockerfile` that packages Apache, PHP 8.3, and an optimized MySQL service directly into the container:

1. Connect this repository to your [Render Dashboard](https://dashboard.render.com).
2. Select **New Web Service** and choose your repository.
3. Choose the **Docker** runtime.
4. Click **Deploy Web Service**. Render builds the container, starts the web and database services, imports the schema, and provides your live public URL.
