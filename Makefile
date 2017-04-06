lint:
	find src -name *.php -exec php -l {} \;
	find test -name *.php -exec php -l {} \;
	find spec -name *.php -exec php -l {} \;
	find examples -name *.php -exec php -l {} \;

cs: lint
	./vendor/bin/phpcbf --standard=PSR2 src test examples
	./vendor/bin/phpcs --standard=PSR2 --warning-severity=0 src test examples
	./vendor/bin/phpcs --standard=Squiz --sniffs=Squiz.Commenting.FunctionComment,Squiz.Commenting.FunctionCommentThrowTag,Squiz.Commenting.ClassComment,Squiz.Commenting.VariableComment src test examples

test: tdd bdd

tdd:
	./vendor/bin/phpunit test

bdd:
	./vendor/bin/phpspec run --format=pretty -v

cover:
	./vendor/bin/phpunit --coverage-html ./cover test
deps:
	wget -q https://getcomposer.org/composer.phar -O ./composer.phar
	chmod +x composer.phar
	php composer.phar install

dist-clean:
	rm -rf bin
	rm -rf vendor
	rm -f composer.phar
	rm -f composer.lock

docker-nats:
	docker run --rm -p 8222:8222 -p 4222:4222 -d --name nats-main nats

phpdoc:
	wget -q http://phpdoc.org/phpDocumentor.phar -O ./phpDocumentor.phar

.PHONY: lint test cs cover deps dist-clean
