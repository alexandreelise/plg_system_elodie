.PHONY: help gen install doc all

.DEFAULT_GOAL=help
APP_CURRENT_DIR=$$(pwd)
APP_CURRENT_DATETIME=$$(date +%Y%m%d%H%M%S)
APP_BUILD_DIR=$$(dirname $(APP_CURRENT_DIR))/build
APP_PHP=/usr/bin/php
APP_COMPOSER=$(HOME)/bin/composer
ifdef APP_PHP_VERSION
APP_PHP=docker run --rm --name phpcli -v $(APP_CURRENT_DIR):/usr/src/myapp -w /usr/src/myapp coderparlerpartager/docker-images:debugbox-$(APP_PHP_VERSION)-fpm
APP_COMPOSER=docker run --rm --name composercli -v $(APP_CURRENT_DIR)/src:/usr/src/myapp -w /usr/src/myapp coderparlerpartager/docker-images:debugbox-$(APP_PHP_VERSION)-fpm --entrypoint /bin/composer
endif

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

gen: ./build ./src 		## Create extension zip file
	cd $(APP_CURRENT_DIR)/src \
	&& find . -type f -name "*.php" -exec $(APP_PHP) -lf "{}" \; \
	&& zip -9 -r $(APP_BUILD_DIR)/$$(basename $$(dirname $(APP_CURRENT_DIR)))_$(APP_CURRENT_DATETIME).zip . \
	&& cd ..

install:  ./composer.json        ## Install composer packages dependencies
	$(APP_COMPOSER) install

./composer.lock: install		## Generate composer.lock file

./build:            ## Create initial build directory
	mkdir -p ./build

./docs:				## Create initial docs directory
	mkdir -p ./docs

./tests:			## Create initial tests directory
	mkdir -p ./tests

doc: ./src ./docs ./tests ./composer.lock		## Generate documentation
	$(HOME)/bin/phpdoc

all: gen doc      ## Generate everything
