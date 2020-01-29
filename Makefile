vendor: composer.lock
	composer install

composer.lock: composer.json
	composer update

.PHONY: check static-analysis unit-tests coding-standards
check: static-analysis unit-tests coding-standards

static-analysis: vendor
	vendor/bin/phpstan analyse

unit-tests: vendor
	vendor/bin/phpunit

coding-standards: vendor
	vendor/bin/phpcs --colors -p
