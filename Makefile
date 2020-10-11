cs:
	bin/php-cs-fixer fix --verbose

test:
	bin/phpunit --configuration=tests/phpunit.xml tests
