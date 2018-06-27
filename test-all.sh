#!/bin/bash
# file: test-all.sh

#Stop process if error
set -e

echo "# Coding standard"
php vendor/bin/ecs check src tests --fix

echo "# Static analysis"
php vendor/bin/phpstan analyse src --level 7

echo "# PHPUnit"
php vendor/bin/phpunit tests
