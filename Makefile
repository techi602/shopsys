generate-schema:
	docker-compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/project-base/app/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker-compose exec -u root storefront chown node:node schema.graphql
	docker-compose exec storefront pnpm run gql
	docker-compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native:
	php phing frontend-api-generate-graphql-schema
	cp project-base/app/schema.graphql project-base/storefront/schema.graphql
	cd project-base/storefront; pnpm run gql
	rm -rf project-base/storefront/schema.graphql

check-schema:
	docker-compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/project-base/app/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker-compose exec -u root storefront chown node:node schema.graphql
	docker-compose exec storefront sh check-code-gen.sh

define run_acceptance_tests
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=test environment-change
	docker compose exec php-fpm php phing test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress
	-docker compose run --rm -e TYPE=$(1) cypress;
	docker compose stop storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef

.PHONY: run-acceptance-tests-base
run-acceptance-tests-base:
	$(call run_acceptance_tests,base)

.PHONY: run-acceptance-tests-actual
run-acceptance-tests-actual:
	$(call run_acceptance_tests,actual)
