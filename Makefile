.DEFAULT_GOAL:=help

.PHONY: bash
bash:
	cd docker && docker-compose exec php-fpm bash

.PHONY: install
install:
	cd docker && docker-compose run --rm php-fpm sh -c 'composer install --no-interaction --no-suggest --ansi'

.PHONY: run-migrations
run-migrations:
	cd docker && docker-compose run --rm php-fpm sh -c 'bin/console doctrine:migrations:migrate'

.PHONY: tests
tests:
	cd docker && docker-compose run --rm php-fpm sh -c 'vendor/bin/phpunit --testdox --colors=always'

.PHONY: unit
unit:
	cd docker && docker-compose run --rm php-fpm sh -c 'vendor/bin/phpunit --testdox --group=Unit --colors=always'

.PHONY: integration
integration:
	cd docker && docker-compose run --rm php-fpm sh -c 'vendor/bin/phpunit --testdox --group=Integration --colors=always'

.PHONY: cs
cs:
	cd docker && docker-compose run --rm php-fpm sh -c 'vendor/bin/php-cs-fixer fix --no-interaction --diff --verbose'

.PHONY: stan
stan:
	cd docker && docker-compose run --rm php-fpm sh -c 'vendor/bin/phpstan analyse --memory-limit=-1'
