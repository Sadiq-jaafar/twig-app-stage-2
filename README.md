# Ticket Management App (Twig Version)

A PHP/Twig implementation of the ticket management system with user authentication and per-user ticket storage.

## Features

- User registration and authentication
- Session management with flash messages
- Per-user ticket management
- Dashboard with ticket statistics
- Responsive design
- Toast notifications
- Form validation
- Matches React/Vue UI exactly

## Requirements

- PHP 8.0 or higher
- Composer
- JSON extension enabled

## Installation

1. Clone the repository
2. Install dependencies:
```bash
cd twig-app
composer install
```

3. Ensure the data directory is writable:
```bash
chmod 777 data
```

4. Start the PHP development server:
```bash
php -S localhost:8000 -t public
```

5. Visit http://localhost:8000 in your browser

## Project Structure

```
twig-app/
├── assets/          # CSS and other static assets
├── data/           # JSON storage for users and tickets
├── public/         # Web root
│   └── index.php   # Front controller
├── src/            # PHP classes
│   ├── Auth.php    # Authentication
│   ├── Storage.php # Ticket storage
│   └── Controller.php # Route handlers
├── templates/      # Twig templates
│   ├── auth/       # Login/signup forms
│   ├── partials/   # Reusable components
│   ├── base.twig   # Base layout
│   ├── dashboard.twig
│   └── tickets.twig
└── composer.json
```

## Usage

1. Create an account via the signup page
2. Log in with your credentials
3. View your dashboard with ticket statistics
4. Create, edit, and delete tickets
5. Log out when finished

## Development

The application uses:
- Twig for templating
- JSON files for data storage
- PHP sessions for auth
- CSS matching React/Vue versions

## Notes

- User data is stored in `data/users.json`
- Tickets are stored per-user in `data/tickets_{userId}.json`
- Sessions expire after 6 hours
- Flash messages auto-dismiss after 3.5 seconds
- Form validation matches React version
- Responsive layout works on all devices

## Testing

1. Register a new account
2. Verify login works
3. Create some tickets
4. Check dashboard stats update
5. Edit and delete tickets
6. Verify toast notifications
7. Test form validation
8. Check responsive layout

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)