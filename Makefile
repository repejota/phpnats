lint:
	find src -name *.php -exec php -l {} \;
	find tests -name *.php -exec php -l {} \;
	find examples -name *.php -exec php -l {} \;

test:
	./vendor/bin/phpunit

cover:
	./vendor/bin/phpunit --coverage-html ./cover

cs:
	./phpcbf.phar src
	./phpcbf.phar tests
	./phpcbf.phar examples
	./phpcs.phar src tests examples

.PHONY: lint test cs cover
