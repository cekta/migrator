.PHONY: dev  shell refresh docs-build

dev:
	docker compose up -d --remove-orphans
shell:
	docker compose up -d --remove-orphans --wait app 
	docker compose exec -it app bash
refresh:
	docker compose down
	docker compose build
docs-build:
	docker compose run --rm pages build