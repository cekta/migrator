down:
	docker-compose down
up:
	docker-compose up -d
refresh: down up
migrate:
	docker-compose exec app php cli.php migrate -i
rollback:
	docker-compose exec app php cli.php migration:rollback