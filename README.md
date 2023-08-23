# 10 secret steps to start
# [ PHP 8.1 + MySQL + Apache + Symfony 6.2 + Docker ]

## Description

This is a complete stack for running Symfony 6.2 into Docker containers using docker-compose tool.

It is composed by 4 containers:

- `apache`, acting as the webserver.
- `php`, the PHP-FPM container with the 8.1 version of PHP.
- `database` which is the MySQL database container with a **MySQL 8.0** image.
- `phpmyadmin` which is the visual tool to manage locally the database.

## Project setup

1 - Take a copy of `.env` in the root directory.

```sh
cp .env .env.local
```

2 - Fill the environment variables as bellow.

```sh
# Symfony env variables
APP_ENV=dev
APP_SECRET=
# Database env variables
DATABASE_URL=
# Mail env variables
MAILER_DSN=
MAILER_FROM=
# Front env variables
FRONT_URL=
```

3 - Go inside the docker folder by typing.

```sh
cd docker
```

4 - Take a copy of `.env.example` in docker directory.

```sh
cp .env.example .env
```

5 - Fill the `.env` file with your own credentials.

```sh
# Mysql env variables
APP_PORT=
APP_DB_ADMIN_PORT=
DB_PORT=

MYSQL_ROOT_PASS=
MYSQL_USER=
MYSQL_PASS=
MYSQL_DB=
```

6 - To start docker services, run the `docker-compose.yml` file by executing this command.

```sh
docker compose up --build -d
```

7 - Finally your project is up running, you can check it by executing this command.

```sh
docker compose ps -a
```

8 - To install dependencies, make sure you are in the same level as `composer.json` inside `apache container` and then
type the command.

```sh
composer install
```

9 - To check if you've properly configured database connection, please type this command in the root directory. The
result will be displayed in the console.

```sh
php bin/console doctrine:database:create
```

10 - To shut down all services, you can execute this command inside docker folder.

```sh
docker compose down
```