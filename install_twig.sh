#!/bin/bash

PHP="/usr/local/Cellar/php54/5.4.16/bin/php"
curl -s http://getcomposer.org/installer  | $PHP
$PHP composer.phar install
