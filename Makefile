up: deps assets db
	docker-compose exec php sh -c "php bin/console messenger:consume async &"
	@docker-compose exec php sh -c "php bin/console d:m:m --no-interaction"
	@echo "The application is ready to go."

down:
	@docker-compose down

deps: containers
	@docker-compose run composer sh -c "composer install --ignore-platform-req=ext-redis && composer dump-autoload --optimize"

assets: containers
	@docker-compose run node sh -c "yarn install && yarn run build"

db: containers
	@docker-compose exec php sh -c "php bin/console d:d:c --no-interaction"
	@docker-compose run php sh -c "wait-for-it.sh database:3306"

containers:
	@docker-compose down
	@docker-compose up -d
