down:
	docker compose down
up:
	docker compose up -d --remove-orphans
build:
	docker compose build
shell: up
	docker compose exec app sh
refresh: down build up
migrate: up
	docker compose exec app php ./tests/bin/cli.php migrate -i
rollback: up
	docker compose exec app php ./tests/bin/cli.php migration:rollback
phpcs:
	./vendor/bin/phpcs
phpstan:
	./vendor/bin/phpstan analyse --memory-limit=-1
phpunit:
	./vendor/bin/phpunit
ci: phpcs phpstan phpunit