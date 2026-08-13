### Install MariaDB
docker run --name mariadb-server -e MARIADB_ROOT_PASSWORD=your_secure_password -p 3306:3306 -v mariadb_data:/var/lib/mysql -d mariadb:latest
### Create Database
docker exec -it mariadb-server mariadb -u root -p -e "CREATE DATABASE your_database_name;"

#### Change .env contents following your database setting