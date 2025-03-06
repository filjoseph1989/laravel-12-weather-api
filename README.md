# Laravel 12 Simple Project Documentation

This is a simple Laravel 12 project featuring user authentication, weather data retrieval via OpenWeatherMap, and email functionality using Mailtrap. It includes API endpoints, console commands, and queue jobs.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation](#installation)
3. [Environment Configuration](#environment-configuration)
4. [Apache2 Configuration](#apache2-configuration)
5. [Database Setup](#database-setup)
6. [Running the Application](#running-the-application)
7. [API Endpoints](#api-endpoints)
8. [Console Commands](#console-commands)
9. [Testing the API](#testing-the-api)
   - [Testing All Endpoints](#testing-all-endpoints)
   - [Running Unit Tests](#running-unit-tests)
10. [Future Improvements](#future-improvements)
11. [Known Issues](#known-issues)

---

## Prerequisites

Before starting, ensure you have the following installed:
- **PHP** >= 8.2
- **Composer**
- **MySQL** or **MariaDB**
- **Apache2** (optional, for production-like environment)
- **Git**
- A **Mailtrap** account ([mailtrap.io](https://mailtrap.io)) for email testing
- An **OpenWeatherMap API key** ([openweathermap.org](https://openweathermap.org))

---

## Installation

1. **Clone the Project from GitHub**
   Open a terminal and clone the repository:
   ```bash
   git clone https://github.com/filjoseph1989/laravel-12-weather-api.git
   cd weather-api
   ```

2. **Install Dependencies**
   All required packages (Laravel 12, Laravel Sanctum, etc.) are defined in `composer.json`. Install them:
   ```bash
   composer install
   ```

---

## Environment Configuration

1. **Edit `.env` File**
   Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
   Update `.env` with your configuration:
   ```env
   APP_NAME="Weather API"
   APP_ENV=local
   APP_KEY= # Run `php artisan key:generate` to set this
   APP_DEBUG=true
   APP_URL=http://weather-api.test

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=weather_api
   DB_USERNAME=root
   DB_PASSWORD=

   # OpenWeatherMap Configuration
   OPENWEATHERMAP_API_KEY=your_openweathermap_api_key
   OPENWEATHERMAP_URL=https://api.openweathermap.org/data/2.5/weather
   DEFAULT_CITY=Perth
   DEFAULT_COUNTRY=AU

   # Mailtrap Configuration
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="no-reply@weather-api.test"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

2. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

---

## Apache2 Configuration

1. **Install Apache2** (Ubuntu)
   ```bash
   sudo apt update
   sudo apt install apache2
   sudo systemctl enable apache2
   sudo systemctl start apache2
   ```

2. **Set Up Virtual Host**
   Create a virtual host file:
   ```bash
   sudo nano /etc/apache2/sites-available/weather-api.test.conf
   ```
   Add:
   ```apache
   <VirtualHost *:80>
       ServerName weather-api.test
       DocumentRoot /var/www/weather-api/public

       <Directory /var/www/weather-api/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/weather-api-error.log
       CustomLog ${APACHE_LOG_DIR}/weather-api-access.log combined
   </VirtualHost>
   ```

3. **Enable the Site**
   ```bash
   sudo a2ensite weather-api.test.conf
   sudo systemctl reload apache2
   ```

4. **Update Hosts File**
   Edit `/etc/hosts`:
   ```bash
   sudo nano /etc/hosts
   ```
   Add:
   ```
   127.0.0.1   weather-api.test
   ```

5. **Move Project Files**
   ```bash
   sudo mv ~/weather-api /var/www/
   sudo chown -R www-data:www-data /var/www/weather-api
   sudo chmod -R 755 /var/www/weather-api
   ```

---

## Database Setup

1. **Create Database**
   Log in to MySQL/MariaDB:
   ```sql
   mysql -u root -p
   CREATE DATABASE weather_api;
   EXIT;
   ```

2. **Run Migrations**
   Ensure `.env` database settings are correct, then:
   ```bash
   php artisan migrate
   ```

3. **Seed Database (Optional)**
   If seeders are available:
   ```bash
   php artisan db:seed
   ```

---

## Running the Application

1. **Development Server**
   For local testing without Apache:
   ```bash
   php artisan serve
   ```
   Access at `http://localhost:8000`.

2. **Production with Apache**
   After Apache setup:
   ```bash
   sudo systemctl start apache2
   ```
   Access at `http://weather-api.test`.

3. **Queue Worker**
   Some features (e.g., email sending) use queues. Start a worker:
   ```bash
   php artisan queue:work
   ```

4. **Scheduler**
   To automate tasks (e.g., cleaning weather data), set up Laravel’s scheduler with a cron job:
   ```bash
   crontab -e
   ```
   Add:
   ```
   * * * * * cd /var/www/weather-api && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## API Endpoints

The API uses Laravel Sanctum for authentication. All endpoints are prefixed with `/api`.

| Method | Endpoint            | Description                     | Middleware         |
|--------|---------------------|---------------------------------|--------------------|
| POST   | `/login`            | Authenticate a user            | None               |
| POST   | `/register`         | Register a new user            | None               |
| GET    | `/user`             | Get authenticated user         | `auth:sanctum`     |
| POST   | `/logout`           | Logout authenticated user      | `auth:sanctum`     |
| GET    | `/weather`          | Fetch current weather          | `auth:sanctum`     |
| GET    | `/posts`            | Get paginated posts            | `auth:sanctum`     |
| GET    | `/posts/{post}`     | Get a specific post            | `auth:sanctum`     |
| POST   | `/posts`            | Create a new post              | `auth:sanctum`     |
| PUT    | `/posts/{id}`       | Update a post                  | `auth:sanctum`     |
| DELETE | `/posts/{id}`       | Delete a post                  | `auth:sanctum`     |
| GET    | `/users/{user}`     | Get user details               | `auth:sanctum`     |

---

## Console Commands

1. **`app:clean-weather-data`**
   - **Purpose**: Deletes weather data older than 7 days.
   - **Run**: `php artisan app:clean-weather-data`

2. **`app:manually-fetch-weather-data`**
   - **Purpose**: Dispatches a job to fetch weather data.
   - **Run**: `php artisan app:manually-fetch-weather-data`

3. **`app:manually-send-welcome-email`**
   - **Purpose**: Sends a welcome email to a user by ID or email.
   - **Run**: `php artisan app:manually-send-welcome-email testuser@example.com`

---

## Testing the API

### Testing All Endpoints

Below are `curl` commands to test each API endpoint. Replace `your_token` with the token obtained from the `/login` response.

1. **Register a User**
   ```bash
   curl -X POST http://weather-api.test/api/register \
   -H "Content-Type: application/json" \
   -d '{"name":"Test User","email":"testuser@example.com","password":"password"}'
   ```
   - **Expected Response**: JSON with user details and token.

2. **Login**
   ```bash
   curl -X POST http://weather-api.test/api/login \
   -H "Content-Type: application/json" \
   -d '{"email":"testuser@example.com","password":"password"}'
   ```
   - **Expected Response**: JSON with token (e.g., `"token": "1|abcdef..."`).
   - Copy the token for authenticated requests.

3. **Get Authenticated User**
   ```bash
   curl -X GET http://weather-api.test/api/user \
   -H "Authorization: Bearer your_token"
   ```
   - **Expected Response**: JSON with user details (e.g., `{"id":1,"name":"Test User",...}`).

4. **Logout**
   ```bash
   curl -X POST http://weather-api.test/api/logout \
   -H "Authorization: Bearer your_token"
   ```
   - **Expected Response**: Success message (e.g., `{"message":"Logged out"}`).

5. **Get Weather**
   ```bash
   curl -X GET http://weather-api.test/api/weather \
   -H "Authorization: Bearer your_token"
   ```
   - **Expected Response**: JSON with weather data for Perth, AU (e.g., `{"main":{"temp":25.5},...}`).

6. **Create a Post**
   ```bash
   curl -X POST http://weather-api.test/api/posts \
   -H "Authorization: Bearer your_token" \
   -H "Content-Type: application/json" \
   -d '{"title":"My First Post","content":"Hello, world!"}'
   ```
   - **Expected Response**: JSON with the created post (e.g., `{"id":1,"title":"My First Post",...}`).

7. **Get Paginated Posts**
   ```bash
   curl -X GET http://weather-api.test/api/posts \
   -H "Authorization: Bearer your_token"
   ```
   - **Expected Response**: JSON with paginated posts (e.g., `{"data":[{"id":1,...}],...}`).

8. **Get a Specific Post**
   ```bash
   curl -X GET http://weather-api.test/api/posts/1 \
   -H "Authorization: Bearer your_token"
   ```
   - **Expected Response**: JSON with post details (e.g., `{"id":1,"title":"My First Post",...}`).

9. **Update a Post**
   ```bash
   curl -X PUT http://weather-api.test/api/posts/1 \
   -H "Authorization: Bearer your_token" \
   -H "Content-Type: application/json" \
   -d '{"title":"Updated Post","content":"Updated content"}'
   ```
   - **Expected Response**: JSON with updated post (e.g., `{"id":1,"title":"Updated Post",...}`).

10. **Delete a Post**
    ```bash
    curl -X DELETE http://weather-api.test/api/posts/1 \
    -H "Authorization: Bearer your_token"
    ```
    - **Expected Response**: Success message (e.g., `{"message":"Post deleted"}`).

11. **Get User Details**
    ```bash
    curl -X GET http://weather-api.test/api/users/1 \
    -H "Authorization: Bearer your_token"
    ```
    - **Expected Response**: JSON with user details (e.g., `{"id":1,"name":"Test User",...}`).

### Running Unit Tests

1. **Set Up Testing Environment**
   - Ensure a testing database is configured in `.env.testing` (or copy `.env` and adjust):
     ```env
     DB_DATABASE=weather_api_test
     ```

2. **Run Migrations for Testing**
   ```bash
   php artisan migrate --env=testing
   ```

3. **Execute Tests**
   Run PHPUnit tests:
   ```bash
   php artisan test
   ```

4. **Debugging Tests**
   - Use `--verbose` for detailed output: `php artisan test --verbose`.

---

## Future Improvements

1. **Historical Weather Data**: Add an endpoint to retrieve stored weather data.
2. **User Preferences**: Allow users to save preferred cities for weather fetches.
3. **Weather Notifications**: Notify users of specific conditions (e.g., high temperature).
4. **API Usage Tracking**: Log and monitor API calls and job performance.
5. **Enhanced Posts Endpoint**:
   - Support query parameters: `?per_page=10&page=2&sort=created_at&order=desc&title=example`.
6. **Post Details Improvements**:
   - Add a policy for authorization.
   - Support relationships: `/api/posts/1?include=user,comments`.
7. **Logout Enhancement**: Add an option to revoke all tokens across devices.

---

## Known Issues

1. **Infinite Recursion Bug**:
   - **Description**: Running `php artisan app:clean-weather-data` may trigger "Maximum call stack size of 8339456 bytes reached. Infinite recursion?" in `app/Console/Commands/CleanWeatherData.php`.
   - **Possible Cause**: Excessive logging or recursive database calls.
   - **Workaround**: Check `Weather::where(...)->delete()` for large datasets; consider chunking:
     ```php
     Weather::where('created_at', '<', now()->subDays(7))->chunk(100, function ($records) {
         $records->each->delete();
     });
     ```

