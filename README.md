# Manufacture Inventory Management System
This project is a web-based Spareparts Procurement Management System designed for PT. MySpareParts (dummy company), a vehicle spare parts supplier. The system streamlines the procurement process across three user roles:
1. Supervisor: Manages and approves spare parts requests.
2. Factory: Submits and tracks requests for necessary parts.
3. Distributor: Fulfills approved requests and manages inventory.
Each user role has a unique dashboard with specific functionality, ensuring efficient and role-specific access to the procurement workflow.
## How to Setup:
1. Initial Environment Development
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```
2. Rename .env.example to .env
3. Set folder Permission
```
 sudo chmod 777 -R .
```
5. Create Docker Container
```
 ./vendor/bin/sail up -d
```
6. Generate Key
```
 vendor/bin/sail artisan key:generate
```
Open
```
 localhost/login
```
