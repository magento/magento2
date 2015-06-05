#usr/bin/bash

#get number of files in directory
NUMBER_OF_SUITES=$1
FOLDERSIZE=$(find ./testsuite/Magento/ -maxdepth 1 -type d|wc -l)
FOLDERSIZE=$((FOLDERSIZE/NUMBER_OF_SUITES))

#get folders
FOLDERS=$(ls  "./testsuite/Magento")

#mysql 5.6
sudo apt-get remove --purge mysql-common mysql-server-5.5 mysql-server-core-5.5 mysql-client-5.5 mysql-client-core-5.5;
sudo apt-get autoremove;
sudo apt-get autoclean;
sudo apt-add-repository ppa:ondrej/mysql-5.6 -y;
sudo apt-get update;
sudo apt-get install mysql-server-5.6 mysql-client-5.6;

#create n testsuites and n databases
i=0
for i in `seq 1 $NUMBER_OF_SUITES`
do
	cp phpunit.xml.dist phpunit.xml.travis$i
	perl -pi -e "s/<directory suffix=\"Test.php\">testsuite<\/directory>//g" phpunit.xml.travis$i
	cp etc/install-config-mysql.php.dist etc/install-config-mysql$i.php
	mysql -uroot -e "SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION; create database magento_integration_tests${i}";
done

#replace Memory Usage Tests in all except testsuite 1
for i in `seq 2 $NUMBER_OF_SUITES`
do
	perl -pi -e 's/<!-- Memory tests run first to prevent influence of other tests on accuracy of memory measurements -->//g' phpunit.xml.travis$i
	perl -pi -e 's/<testsuite name="Memory Usage Tests">//g' phpunit.xml.travis$i
	perl -pi -e 's/<file>testsuite\/Magento\/MemoryUsageTest.php<\/file>//g' phpunit.xml.travis$i
	perl -pi -e 's/<\/testsuite>//g' phpunit.xml.travis$i
	perl -pi -e "s/<\/testsuites>/<\/testsuite><\/testsuites>/g" phpunit.xml.travis$i
done

#create list of folders on which tests are to be run
i=0
j=1
for FOLDER in $FOLDERS
do
	FILE[j]+="\n<directory suffix=\"Test.php\">testsuite\/Magento\/${FOLDER}<\/directory>"
	i=$((i+1))
	if [ "$i" -eq "$FOLDERSIZE" ] && [ "$j" -ne "$NUMBER_OF_SUITES" ]; then
		j=$((j+1))
		i=0
	fi
done

# finally replacing in config files.
for i in `seq 1 $NUMBER_OF_SUITES`
do
	perl -pi -e "s/<directory suffix=\"Test.php\">..\/..\/..\/update\/dev\/tests\/integration\/testsuite<\/directory>/${FILE[i]}/g" phpunit.xml.travis$i
	perl -pi -e "s/magento_integration_tests/magento_integration_tests${i}/g" etc/install-config-mysql${i}.php
	perl -pi -e "s/etc\/install-config-mysql.php/etc\/install-config-mysql${i}.php/g" phpunit.xml.travis$i
done