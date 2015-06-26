lint:
	find src -name *.php -exec php -l {} \;

test:
	./vendor/bin/phpunit

cover:
	./vendor/bin/phpunit --coverage-html ./cover
