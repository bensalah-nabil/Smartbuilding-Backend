migration:
	echo "y" | sudo docker exec -i smart_building_backend sh -c "php bin/console make:migration && php bin/console doctrine:migrations:migrate"

build:

	sudo docker-compose build

up:

	sudo docker-compose up 

down:

	sudo docker-compose down

ps:

	sudo docker-compose ps
