#!/bin/bash -e

echo "begin hook ${0}"

echo 'run php-fpm'
php-fpm

echo "end hook ${0}"
