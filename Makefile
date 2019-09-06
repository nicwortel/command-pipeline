vendor: composer.lock
	composer install

composer.lock: composer.json
	composer update

.PHONY: check lint static-analysis unit-tests coding-standards
check: lint static-analysis unit-tests coding-standards

lint: vendor
	vendor/bin/parallel-lint src

static-analysis: vendor
	vendor/bin/phpstan analyse --level=7 src/

unit-tests: vendor
	vendor/bin/phpunit

coding-standards: vendor
	vendor/bin/phpcs --colors -p
