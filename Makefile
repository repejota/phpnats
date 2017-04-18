lint: lint-php lint-psr2 lint-squiz

lint-php:
	find src -name *.php -exec php -l {} \;
	find test -name *.php -exec php -l {} \;
	find spec -name *.php -exec php -l {} \;
	find examples -name *.php -exec php -l {} \;

lint-psr2:
	#./vendor/bin/phpcbf --standard=PSR2 src test examples
	./vendor/bin/phpcs --standard=PSR2 --colors -w -s --warning-severity=0 src test examples

lint-squiz:
	# ./vendor/bin/phpcbf --standard=Squiz,./ruleset.xml src test examples
	./vendor/bin/phpcs --standard=Squiz,./ruleset.xml --colors -w -s --warning-severity=0 src test examples


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
	rm -f phpDocumentor.phar
	rm -rf docs/api

docker-nats:
	docker run --rm -p 8222:8222 -p 4222:4222 -d --name nats-main nats

phpdoc:
	wget -q https://github.com/phpDocumentor/phpDocumentor2/releases/download/v2.9.0/phpDocumentor.phar -O ./phpDocumentor.phar
	chmod +x phpDocumentor.phar
	./phpDocumentor.phar -d ./src/ -t ./docs/api --template=checkstyle --template=responsive-twig

serve-phpdoc:
	cd docs/api && php -S localhost:8000 && cd ../..

.PHONY: lint test cs cover deps dist-clean
