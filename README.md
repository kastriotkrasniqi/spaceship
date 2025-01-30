# Space Trader

A web application for managing your space trading empire.

## Overview

Space Trader is a comprehensive space trading simulation that combines resource management, strategic planning, and dynamic event handling. Take control of your fleet, negotiate trade agreements, and navigate through challenging space conditions to become a successful space trading mogul.

## Core Features

### Fleet Management
- Create and manage multiple starships with unique capabilities
- Monitor ship status and maintenance requirements
- Assign ships to specific trade routes

### Resource Trading System
- Manage diverse space resources and commodities
- Real-time inventory tracking across multiple locations
- Dynamic pricing based on supply and demand

### Interplanetary Commerce
- Establish trade agreements between different planets
- Create and optimize trade routes
- Monitor and analyze trade performance

### Dynamic Events
- Real-time event simulation (meteor storms, space weather)
- Route adjustments based on environmental conditions
- Risk management and contingency planning

### Analytics and Reporting
- Comprehensive trade statistics
- Performance metrics for routes and ships
- Resource demand analysis

### Multi-User Support
- Role-based access control
- Customizable user permissions
- Secure authentication system

## Technical Requirements

- PHP 8.1 or higher
- Composer
- SQLite/MySQL/PostgreSQL
- Node.js and NPM for frontend assets

## Installation

1. Clone the repository:
   ```bash
   git clone [https://github.com/kastriotkrasniqi/spaceship.git]
   cd spaceship
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   - Copy `.env.example` to `.env`
   - Update database credentials in `.env`
   - Generate application key: `php artisan key:generate`

4. Set up the database:
   ```bash
   php artisan migrate
   php artisan db:seed

## Command Line Interface

### Planets Management
- View planets: `php artisan planets` → Show Planets
- Add planet: `php artisan planets` → Add Planet
- Edit planet: `php artisan planets` → Update Planet
- Delete planet: `php artisan planets` → Delete Planet

### Starship Operations
- View fleet: `php artisan starships` → Show Starships
- Add ship: `php artisan starships` → Add Starship
- Edit ship: `php artisan starships` → Update Starship
- Delete ship: `php artisan starships` → Delete Starship

### Resource Management
- View resources: `php artisan resources` → Show Resources
- Add resource: `php artisan resources` → Add Resource
- Edit resource: `php artisan resources` → Update Resource
- Delete resource: `php artisan resources` → Delete Resource

### Trade Agreements
- View agreements: `php artisan trade-agreements` → Show Trade Agreements
- Create agreement: `php artisan trade-agreements` → Add Trade Agreement
- Edit agreement: `php artisan trade-agreements` → Update Trade Agreement
- Delete agreement: `php artisan trade-agreements` → Delete Trade Agreement

### Inventory Control
- View inventory: `php artisan inventories` → Show Inventories
- Add inventory: `php artisan inventories` → Add Inventory
- Edit inventory: `php artisan inventories` → Update Inventory
- Delete inventory: `php artisan inventories` → Delete Inventory

### Analytics
- View trade analytics: `php artisan trade-analytics` → Show Trade Analytics


### Space Weather
- View space weather: `php artisan space-weather` → Show Space Weather

### Track Starships
- View starships: `php artisan track-starships` → Show Starships


## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
