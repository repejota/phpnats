CLEAN_FILES = bin build cover vendor docs/api composer.phar composer.lock phpDocumentor.phar
SOURCE_CODE_PATHS = src test examples
API_DOCS_PATH = ./docs/api
COVERAGE_PATH = ./cover

lint: lint-php lint-psr2 lint-squiz

.PHONY: lint-php
lint-php:
	find $(SOURCE_CODE_PATHS) spec -name *.php -exec php -l {} \;

.PHONY: lint-psr2
lint-psr2:
	# wget -qhttps://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar -O ./phpcbf.phar
	# ./vendor/bin/phpcbf --standard=PSR2 $(CODE_SOURCES)
	wget -q https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar -O ./phpcs.phar
	chmod +x phpcs.phar
	./phpcs.phar --standard=PSR2 --colors -w -s --warning-severity=0 $(SOURCE_CODE_PATHS)

.PHONY: lint-squiz
lint-squiz:
	# wget -qhttps://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar -O ./phpcbf.phar
	# ./vendor/bin/phpcbf --standard=Squiz,./ruleset.xml $(CODE_SOURCES)
	wget -q https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar -O ./phpcs.phar
	chmod +x phpcs.phar
	./phpcs.phar --standard=Squiz,./ruleset.xml --colors -w -s --warning-severity=0 $(SOURCE_CODE_PATHS)


test: test-tdd test-bdd

.PHONY: test-tdd
test-tdd:
	./vendor/bin/phpunit test

.PHONY: test-bdd
test-bdd:
	./vendor/bin/phpspec run --format=pretty -v

cover:
	./vendor/bin/phpunit --coverage-html $(COVERAGE_PATH) test

deps:
	wget -q https://getcomposer.org/composer.phar -O ./composer.phar
	chmod +x composer.phar
	./composer.phar install

dist-clean:
	rm -rf $(CLEAN_FILES)

docker-nats:
	docker run --rm -p 8222:8222 -p 4222:4222 -d --name nats-main nats

phpdoc:
	wget -q https://github.com/phpDocumentor/phpDocumentor2/releases/download/v2.9.0/phpDocumentor.phar -O ./phpDocumentor.phar
	chmod +x phpDocumentor.phar
	./phpDocumentor.phar -d ./src/ -t $(API_DOCS_PATH) --template=checkstyle --template=responsive-twig

serve-phpdoc:
	cd $(API_DOCS_PATH) && php -S localhost:8000 && cd ../..
