lint:
	find src -name *.php -exec php -l {} \;
	find tests -name *.php -exec php -l {} \;
	find examples -name *.php -exec php -l {} \;

cs: lint
	./bin/phpcbf --standard=PSR2 src tests examples
	./bin/phpcs --standard=PSR2 --warning-severity=0 src tests examples
	./bin/phpcs --standard=Squiz --sniffs=Squiz.Commenting.FunctionComment,Squiz.Commenting.FunctionCommentThrowTag,Squiz.Commenting.ClassComment,Squiz.Commenting.VariableComment src tests examples

test:
	./bin/phpunit test

bdd:
	./bin/phpspec run

cover:
	./bin/phpunit --coverage-html ./cover test
deps:
	wget -q https://getcomposer.org/composer.phar -O ./composer.phar
	chmod +x composer.phar
	php composer.phar install

dist-clean:
	rm -rf bin
	rm -rf vendor
	rm -f composer.phar
	rm -f composer.lock

.PHONY: lint test cs cover deps dist-clean
