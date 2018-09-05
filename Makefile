PHPCS_PHAR = https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
COMPOSER_PHAR = https://getcomposer.org/composer.phar
PHPDOCUMENTOR_PHAR_URL = https://github.com/phpDocumentor/phpDocumentor2/releases/download/v2.9.0/phpDocumentor.phar
CLEAN_FILES = composer.phar composer.lock phpdoc.phar phpcs.phar phpcbf.phar .idea
CLEAN_FOLDERS = bin build cover vendor docs/api
CLEAN_PATHS = $(CLEAN_FILES) $(CLEAN_FOLDERS)
SOURCE_CODE_PATHS = src test examples
API_DOCS_PATH = ./docs/api
COVERAGE_PATH = ./cover

define require_phar
	@[ -f ./$(1) ] || wget -q $(2) -O ./$(1) && chmod +x $(1);
endef

lint:		## Lint source code
lint: lint-php lint-psr2 lint-squiz

.PHONY: lint-php
lint-php:
	find $(SOURCE_CODE_PATHS) spec -name *.php -exec php -l {} \;

.PHONY: lint-psr2
lint-psr2:
	$(call require_phar,phpcs.phar,$(PHPCS_PHAR))
	./phpcs.phar --standard=PSR2 --colors -w -s --warning-severity=0 $(SOURCE_CODE_PATHS)

.PHONY: lint-squiz
lint-squiz:
	$(call require_phar,phpcs.phar,$(PHPCS_PHAR))
	./phpcs.phar --standard=Squiz,./ruleset.xml --colors -w -s --warning-severity=0 $(SOURCE_CODE_PATHS)


test:		## Execute all tests suites TDD and BDD
test: test-tdd test-bdd

.PHONY: test-tdd
test-tdd:
	./vendor/bin/phpunit test

.PHONY: test-bdd
test-bdd:
	./vendor/bin/phpspec run --format=pretty -v

cover:		## Generate coverage report
	./vendor/bin/phpunit --coverage-html $(COVERAGE_PATH) test

deps:		## Install dependencies
	$(call require_phar,composer.phar,$(COMPOSER_PHAR))
	./composer.phar install --no-dev

dev-deps:	## Install development dependencies
	$(call require_phar,composer.phar,$(COMPOSER_PHAR))
	./composer.phar install

dist-clean:	## Clean developer files, dependenciers and temporary files
	rm -rf $(CLEAN_PATHS)

docker-nats:	## Start NATS container ( for testing purposes )
	docker run --rm -p 8222:8222 -p 4222:4222 -d --name nats-main nats

phpdoc:		## Generate phpdoc API documentation
	$(call require_phar,phpdoc.phar,$(PHPDOCUMENTOR_PHAR_URL))
	./phpdoc.phar -d ./src/ -t $(API_DOCS_PATH) --template=checkstyle --template=responsive-twig

serve-phpdoc:	## Serve phpdoc API documentation at http;://localhost:8000
	cd $(API_DOCS_PATH) && php -S localhost:8000 && cd ../..

include Makefile.help.mk

