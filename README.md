# Event Spot Backend

Event Spot Backend is a comprehensive Laravel RESTful API for event management and discovery. It provides robust endpoints for event operations, user management, and payment processing, built with Laravel's best practices and modern API design principles.

## 🚀 Features

-   **User Authentication**: Secure registration, login, and token-based authentication
-   **Event Management**: CRUD operations for events with image handling
-   **Category System**: Organize events into categories
-   **Comment System**: User comments and replies on events
-   **Booking System**: Event registration and payment processing
-   **Promoter Dashboard**: Special endpoints for event organizers
-   **Search & Filtering**: Advanced event filtering by category, date, and price
-   **File Management**: Secure handling of event images and documents

## 🛠️ Tech Stack

-   **Laravel 12**: PHP framework for robust API development
-   **Sanctum**: Token-based authentication
-   **MySQL**: Relational database
-   **File Storage**: Local/Cloud storage for media files
-   **API Documentation**: OpenAPI/Swagger integration

## 🚀 Getting Started

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   MySQL 5.7 or higher
-   Node.js & NPM (for frontend assets)
-   Git

### Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/feryndka/event_spot_be
    cd event_spot_be
    ```

2. Install PHP dependencies:

    ```bash
    composer install
    ```

3. Create environment file:

    ```bash
    cp .env.example .env
    ```

4. Generate application key:

    ```bash
    php artisan key:generate
    ```

5. Configure database in `.env`:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=event_spot_be
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6. Run migrations:

    ```bash
    php artisan migrate
    ```

7. Start the development server:
    ```bash
    php artisan serve
    ```

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

All protected routes require a Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

### Endpoints

#### Authentication

-   `POST /register` - Register new user
-   `POST /login` - User login
-   `POST /logout` - User logout
-   `GET /me` - Get current user info

#### Events

-   `GET /events` - List all events
-   `GET /events/{id}` - Get event details
-   `POST /events/{id}/register` - Register for an event
-   `GET /events/{id}/comments` - Get event comments

#### Promoter Endpoints

-   `GET /promotor/events` - List promoter's events
-   `POST /promotor/events` - Create new event
-   `PUT /promotor/events/{id}` - Update event
-   `DELETE /promotor/events/{id}` - Delete event
-   `POST /promotor/events/{id}/publish` - Publish event
-   `POST /promotor/events/{id}/unpublish` - Unpublish event
-   `GET /promotor/events/{id}/attendees` - Get event attendees
-   `POST /promotor/events/{id}/attendees/{attendee}/check-in` - Check-in attendee

#### Categories

-   `GET /categories` - List all categories
-   `GET /categories/{id}` - Get category details

#### Comments

-   `POST /events/{id}/comments` - Create comment
-   `PUT /comments/{id}` - Update comment
-   `DELETE /comments/{id}` - Delete comment

## 🏗️ Project Structure

```
app/
├── Console/          # Artisan commands
├── Exceptions/       # Exception handlers
├── Http/            # HTTP layer
│   ├── Controllers/ # API controllers
│   ├── Middleware/  # Custom middleware
│   └── Requests/    # Form requests
├── Models/          # Eloquent models
├── Providers/       # Service providers
└── Services/        # Business logic
config/              # Configuration files
database/
├── factories/       # Model factories
├── migrations/      # Database migrations
└── seeders/         # Database seeders
routes/              # API routes
storage/             # File storage
tests/               # Test files
```

## 🔒 Security

-   Token-based authentication using Laravel Sanctum
-   CSRF protection
-   Input validation
-   SQL injection prevention
-   XSS protection
-   Rate limiting on sensitive endpoints

## 📦 Dependencies

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^3.2",
        "intervention/image": "^2.7",
        "midtrans/midtrans-php": "^2.5"
    }
}
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 🔮 Roadmap

-   [ ] Implement real-time notifications
-   [ ] Add payment gateway integration
-   [ ] Implement event analytics
-   [ ] Add social media sharing
-   [ ] Implement event recommendations
-   [ ] Add bulk event operations
-   [ ] Implement event export functionality
-   [ ] Add admin dashboard API endpoints

## 🛠️ Development Guidelines

### Code Style

-   Follow PSR-12 coding standards
-   Use type hints and return types
-   Write meaningful commit messages
-   Document complex logic

### Testing

-   Write unit tests for new features
-   Test edge cases
-   Maintain test coverage above 80%

### Security

-   Never commit sensitive data
-   Use environment variables for configuration
-   Validate all user input
-   Sanitize output
-   Use prepared statements for database queries

### Performance

-   Use eager loading for relationships
-   Implement caching where appropriate
-   Optimize database queries
-   Use pagination for large datasets

### Documentation

-   Keep API documentation up to date
-   Document complex business logic
-   Include examples in documentation
-   Maintain changelog
