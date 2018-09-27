vendor: composer.json $(wildcard composer.lock)
	composer install

.PHONY: check lint static-analysis coding-standards
check: lint static-analysis coding-standards

lint: vendor
	vendor/bin/parallel-lint src

static-analysis: vendor
	vendor/bin/phpstan analyse --level=7 src/

coding-standards: vendor
	vendor/bin/phpcs --colors -p
