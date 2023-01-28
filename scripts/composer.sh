#!/bin/bash

PHP_IMAGE=php:8.1-cli

if [ -t 0 ]
then
	TTY_DOCKER=-it
else
	TTY_DOCKER=
fi

docker run ${TTY_DOCKER} --rm -v ${PWD}:/var/www/html -v ${PWD}/.composer:/.composer -w /var/www/html ${PHP_IMAGE} \
composer "$@"
