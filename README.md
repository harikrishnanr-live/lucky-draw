# Lucky Draw Admin Login System

## Quick Start

```bash
# Navigate to project
cd /Users/harikrishnanr/Documents/Projects/lucky-draw

# Run migrations
php config/run_migrations.php

# Seed admin user
php config/seed_admin.php

# Start server with router
php -S localhost:8000 router.php

# Access admin login
open http://localhost:8000/admin
```

## Admin Login Credentials

- **Username:** `admin@arameglobal.com`
- **Password:** `admin@arameglobal.com`

**Note:** Password is stored as a secure bcrypt hash in the database.
