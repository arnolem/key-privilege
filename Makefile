unit-tests:
	php bin/phpunit --testsuite unit

acceptance-tests:
	vendor/bin/behat

functional-tests:
	php bin/phpunit --testsuite functional

.PHONY: vendor
analyze:
	npm audit
	composer valid
	php bin/console doctrine:schema:valid --skip-sync
	php bin/phpcs
	php vendor/bin/phpstan analyse -c phpstan.neon src --level 7 --no-progress

.PHONY: vendor
analyze-windows:
	npm audit
	composer valid
	php bin/console doctrine:schema:valid --skip-sync
	php bin/phpcs
	vendor\bin\phpstan.bat analyse -c phpstan.neon src --level 7 --no-progress

.PHONY: tests
tests:
	vendor/bin/behat
	php bin/phpunit

fixtures-test:
	php bin/console doctrine:fixtures:load -n --env=test

fixtures-dev:
	php bin/console doctrine:fixtures:load -n --env=dev

database-test:
	php bin/console doctrine:database:drop --if-exists --force --env=test
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:schema:update --force --env=test

database-dev:
	php bin/console doctrine:database:drop --if-exists --force --env=dev
	php bin/console doctrine:database:create --env=dev
	php bin/console doctrine:schema:update --force --env=dev

prepare-test:
	make database-test
	make fixtures-test

prepare-dev:
	make database-dev
	make fixtures-dev

prepare-build:
	make database-test
	make fixtures-test
	npm run dev

install:
	composer install
	npm install
	cp .env.dist .env.dev.local
	cp .env.dist .env.test.local
.PHONY: install

deploy:
	composer install
	npm install
	make database-dev
	make fixtures-dev
	npm run build
