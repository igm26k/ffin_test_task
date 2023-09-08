init: build run composer run_tests
	docker ps

build:
	docker-compose build

run:
	docker-compose up -d

composer:
	docker exec cbr composer install

run_tests:
	docker exec cbr php /var/www/app/bin/phpunit

clear:
	docker-compose stop
	docker-compose rm -f

clear_vendor:
	rm -rf ./vendor

clear_var:
	rm -rf ./var

clear_all: clear clear_var clear_vendor