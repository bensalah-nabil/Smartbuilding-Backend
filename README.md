# 10 secret steps to start
# [ PHP 8.1 + MySQL + Apache + Symfony 6.2 + Docker ]

10 - To shut down all services, you can execute this command inside docker folder.

```sh
sudo docker compose up
```

```sh
sudo docker exec -it backend sh
```

```sh
php bin/console make:migration
```

```sh
php bin/console doctrine:migrations:migrate
```
