#!/usr/bin/env bash

docker run -it --rm -u ${UID} -v `pwd`:/var/www/html -v `pwd`/.composer:/.composer -w /var/www/html php:8.1-cli vendor/bin/simple-phpunit
