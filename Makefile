lint:
	find src -name *.php -exec php -l {} \;
	find tests -name *.php -exec php -l {} \;
	find examples -name *.php -exec php -l {} \;

test:
	./vendor/bin/phpunit

cover:
	./vendor/bin/phpunit --coverage-html ./cover

cs:
	./vendor/bin/phpcbf src tests examples
	./vendor/bin/phpcs src tests examples

.PHONY: lint test cs cover
