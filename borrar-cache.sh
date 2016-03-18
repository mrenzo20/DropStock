#!/bin/bash

sudo chown -R carles:www-data . ; php app/console cache:clear  -e prod ; sudo chown -R carles:www-data . ; sudo chown -R www-data:www-data app/cache/
sudo chown -R carles:www-data . ; php app/console cache:clear  -e dev  ; sudo chown -R carles:www-data . ; sudo chown -R www-data:www-data app/cache/
