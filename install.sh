#!/bin/bash

# A simple bash script to Setup and Install Magento 2.
# Add more options as necessary.

options=(
	--admin-firstname=Joe
	--admin-lastname=Bloggs
	--admin-email=joebloggs@example.com
	--admin-user=joebloggs
	--admin-password=PaS5W0Rd
	--db-name=magento2
	--db-user=dbuser
	--db-password=PaS5W0Rd
)

./bin/magento setup:install ${options[*]}