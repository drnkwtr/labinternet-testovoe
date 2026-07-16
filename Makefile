EXEC_APP    = docker compose exec -T app
EXEC_APP_IT = docker compose exec app

# Docker

build:
	docker compose build --progress plain

up:
	docker compose up -d

down:
	docker compose down

restart: down up

setup:
	docker compose up -d --wait
	$(EXEC_APP) composer install --optimize-autoloader
	$(EXEC_APP) php artisan migrate --force
	$(EXEC_APP) php artisan l5-swagger:generate

install:
	$(EXEC_APP) composer install --optimize-autoloader

# App

migrate:
	$(EXEC_APP) php artisan migrate --force

fresh:
	$(EXEC_APP) php artisan migrate:fresh --force

swagger:
	$(EXEC_APP) php artisan l5-swagger:generate

optimize:
	$(EXEC_APP) php artisan optimize:clear

tinker:
	$(EXEC_APP_IT) php artisan tinker

exec:
	$(EXEC_APP_IT) sh

logs:
	docker compose logs -f app

# Quality

test:
	$(EXEC_APP) php artisan test

pint:
	$(EXEC_APP) ./vendor/bin/pint

pint-test:
	$(EXEC_APP) ./vendor/bin/pint --test

check: pint test

.PHONY: build up down restart setup install \
        migrate fresh swagger optimize tinker exec logs \
        test pint pint-test check
