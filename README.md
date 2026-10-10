# Delta State Election Results Portal (PHP Edition)

An interactive, responsive election results collation and reporting portal for Delta State, built with clean, modern PHP and PDO using MySQL as the primary database.

![Delta State Election Portal Preview](public/img/dashboard-preview.png)

---

## Key Highlights

- **Pure Standalone PHP**: Built with modern PHP 8.x and PDO without framework overhead or complex external dependencies.
- **MySQL as Primary Database**: Tailored for standard MySQL environments (`bincom_test`) with pre-seeded SQL data (`bincom_test.sql`) and automated setup script (`setup_mysql.php`).
- **Zero-Config Cloud MySQL on Render**: The included Docker environment bundles an internal, memory-optimized MySQL engine that automatically starts and imports `bincom_test.sql` on boot. Zero external database subscriptions required.
- **Complete Feature Parity**: Covers all three technical interview assessment tasks plus an executive statewide summary dashboard.
- **Accredited Agent Attribution**: Integrates with the `agentname` table to track the party representative or presiding officer for every submitted voting center.
- **Audit Verification Check**: Compares the sum of individual voting center returns against declared council-level totals side by side.
- **Mobile Responsive & Accessible**: Designed with Bootstrap 5.3, custom styling, interactive Chart.js visualizations, day and night mode switching, and one-click CSV report downloads.

---

## Core Capabilities

### 1. Polling Unit Results Explorer (`polling-unit.php`)
- **3-Tier Cascade Selectors**: Filter by Local Government Area (LGA), then Ward, then Voting Center.
- **Instant Search**: Type any voting center name, landmark, or electoral code to jump straight to results.
- **Party Vote Breakdown**: View each party's score, calculated percentage share, and visual progress bars.
- **Accredited Representative Details**: Displays agent names, contact info, and timestamps recorded for that unit.
- **Visual Chart & Data Export**: Interactive bar chart plus a one-click button to download returns as a CSV file.

### 2. Local Government Collation & Audit (`lga-total.php`)
- **Real-Time Collation**: Select any of Delta State's 25 local government councils to calculate the grand total across all voting centers.
- **Council Distribution Chart**: Doughnut chart highlighting the relative vote share for every political party in that council.
- **Results Verification Check**: A dedicated side-by-side audit table comparing the summed voting center votes with declared council figures, flagging matching records and variances.
- **Spreadsheet Export**: Export full council collation tables to CSV for auditing.

### 3. Enter New Voting Center Results (`new-polling-unit.php`)
- **Dynamic Ward Dropdown**: Select an LGA to instantly populate its specific wards via an internal API.
- **Live Vote Calculator**: Watch total votes tally in real time as you input numbers for each political party.
- **Accredited Agent Capture**: Optional fields to document the agent or presiding officer (`agentname`) who supervised the count.
- **Atomic Database Transactions**: Uses PDO database transactions (`beginTransaction`, `commit`, `rollBack`) to guarantee data integrity across multiple tables.

### 4. Statewide Dashboard (`index.php`)
- **Election Summary**: Total votes cast, total voting centers recorded, and top-ranking parties across Delta State.
- **25-LGA Directory**: Interactive search and status badges across all local government areas.
- **Recent Submissions Feed**: Lists newly submitted voting centers with quick links to view their detailed returns.

---

## Project Structure

```text
bincom-php/
├── api/
│   ├── polling-units.php        # JSON endpoint: voting centers by ward and LGA
│   ├── search.php               # JSON endpoint: instant search autocomplete
│   └── wards.php                # JSON endpoint: wards by LGA
├── config/
│   └── db.php                   # Database handler: MySQL with SQLite fallback
├── includes/
│   ├── footer.php               # Page footer, scripts, and theme switcher
│   ├── header.php               # HTML head, styles, and anti-flicker theme script
│   └── navbar.php               # Navigation header and day/night mode toggle
├── public/
│   ├── css/
│   │   └── custom.css           # Responsive styling and theme rules
│   └── img/
│       └── dashboard-preview.png # Portal screenshot preview
├── .dockerignore                # Exclusions for Docker image builds
├── .htaccess                    # Apache security rules
├── bincom_test.sql              # Complete MySQL database export
├── database.sqlite              # Bundled portable SQLite database
├── Dockerfile                   # Production container with Apache, PHP 8.3 & MySQL
├── docker-entrypoint.sh         # Auto-initializes MySQL and imports bincom_test.sql
├── index.php                    # Statewide dashboard
├── lga-total.php                # Local government collation & audit check
├── new-polling-unit.php         # New voting center submission form
├── polling-unit.php             # Polling unit results explorer
├── render.yaml                  # Render deployment blueprint
├── setup_mysql.php              # Automated MySQL database creator & importer
└── README.md                    # Project documentation
```

---

## Deploying on Render (Easiest MySQL Configuration)

Render does not offer a free managed MySQL service on its dashboard. To solve this effortlessly without requiring any external accounts, the project's **`Dockerfile` bundles a lightweight MySQL (MariaDB) service inside the container**.

When deployed on Render:
1. The container boots both the Apache web server and the internal MySQL server.
2. The startup script automatically creates the `bincom_test` database and imports `bincom_test.sql`.
3. The PHP application connects directly to MySQL via `pdo_mysql`.
4. The entire setup runs within Render's free tier limits with zero external configuration.

### Deployment Steps:
1. Push `bincom-php` to a GitHub repository.
2. Open your [Render Dashboard](https://dashboard.render.com).
3. Click **New +** and select **Web Service**.
4. Choose **Build and deploy from a Git repository** and connect your repository.
5. Set the following:
   - **Name**: `bincom-election-portal-php`
   - **Runtime**: **Docker** (Render detects the `Dockerfile` automatically)
   - **Instance Type**: **Free**
6. Click **Deploy Web Service**. Render builds the image, starts MySQL, imports the election returns, and provides your live URL.

*(Optional Remote MySQL)*: If you prefer to connect an external cloud MySQL database (such as TiDB Cloud, Aiven, or Clever Cloud), you can simply provide `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` under Render's Environment Variables tab. The container will automatically route queries to your remote database instead of the local service.

---

## Local Development Setup

### Default Credentials (`config/db.php`)
- **Host**: `127.0.0.1` (or `localhost`)
- **Port**: `3306`
- **Database**: `bincom_test`
- **Username**: `root`
- **Password**: *(blank)*

### Step 1: Create and Populate MySQL Locally

Choose any of the three quick methods below:

#### Method 1: Automated Script (Recommended)
Run the included helper in your terminal:
```bash
php setup_mysql.php
```
Or visit it in your browser:
```
http://localhost/bincom-php/setup_mysql.php
```

#### Method 2: MySQL Command Line
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS bincom_test;"
mysql -u root -p bincom_test < bincom_test.sql
```

#### Method 3: phpMyAdmin
1. Open phpMyAdmin (`http://localhost/phpmyadmin`).
2. Create a database named `bincom_test`.
3. Go to the **Import** tab, select `bincom_test.sql`, and click **Import**.

---

### Step 2: Run the Local Server

#### Option A: Using XAMPP, WAMP, or Laragon
1. Copy `bincom-php` into your server root directory (`htdocs/bincom-php` or `www/bincom-php`).
2. Start Apache and MySQL from your control panel.
3. Open your browser and navigate to:
   ```
   http://localhost/bincom-php
   ```

#### Option B: Using the PHP Built-In Web Server
```bash
cd bincom-php
php -S localhost:8000
```
Open `http://localhost:8000` in your web browser.

---

## Technical Architecture

- **Backend**: Native PHP 8.x with PDO (PHP Data Objects).
- **Database**: MySQL with pre-loaded election schema and ACID transactions.
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, Chart.js, and custom CSS.
- **Portability**: Containerized with Docker for one-click deployment on Render.
- **Security**: Prepared statements across all SQL queries to eliminate SQL injection.
