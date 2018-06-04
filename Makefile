vendor: composer.json $(wildcard composer.lock)
	composer install

.PHONY: check lint
check: lint

lint: vendor
	vendor/bin/parallel-lint src
