.PHONY: init start down php composer

# Docker Compose Commands
DC_CMD = docker-compose

# Main Commands
init:
	@$(DC_CMD) up -d --build && \
	docker exec -it php /bin/sh -c "composer install && php bin/console doctrine:migrations:migrate"

start:
	@$(DC_CMD) up -d
down:
	@$(DC_CMD) down
php:
	@$(DC_CMD) exec php bash $(filter-out $@,$(MAKECMDGOALS))

# Composer Command with Arguments
composer:
	@$(DC_CMD) exec php composer $(filter-out $@,$(MAKECMDGOALS))

# Prevents Make from treating arguments as separate targets
%:
	@:
