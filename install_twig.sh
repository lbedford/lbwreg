#!/bin/bash

curl -s http://getcomposer.org/installer  | /usr/local/Cellar/php54/5.4.16/bin/php
/usr/local/bin/Cellar/php54/5.4.16/bin/php composer.phar install
