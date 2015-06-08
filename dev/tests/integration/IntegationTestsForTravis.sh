#!usr/bin/bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

# Get number of files in directory
NUMBER_OF_SUITES=$1
FOLDERSIZE=$(find ./testsuite/Magento/ -maxdepth 1 -type d|wc -l)
FOLDERSIZE=$((FOLDERSIZE/NUMBER_OF_SUITES))

# Get folders
FOLDERS=$(ls  "./testsuite/Magento")

# Create n testsuites
i=0
for i in `seq 1 $NUMBER_OF_SUITES`
do
	cp phpunit.xml.dist phpunit.xml.travis$i
done

# Replace Memory Usage Tests in all except testsuite 1
for i in `seq 2 $NUMBER_OF_SUITES`
do
	perl -i -0pe 's/(<!-- Memory)(.*?)(<\/testsuite>)//ims' phpunit.xml.travis$i
done

# Create list of folders on which tests are to be run
i=0
j=1
for FOLDER in $FOLDERS
do
	FILE[j]+="\n<directory suffix=\"Test.php\">testsuite\/Magento\/${FOLDER}<\/directory>"
	i=$((i+1))
	if [ "$i" -eq "$FOLDERSIZE" ] && [ "$j" -lt "$NUMBER_OF_SUITES" ]; then
		j=$((j+1))
		i=0
	fi
done

# Finally replacing in config files.
for i in `seq 1 $NUMBER_OF_SUITES`
do
	perl -pi -e "s/<directory suffix=\"Test.php\">testsuite<\/directory>/${FILE[i]}/g" phpunit.xml.travis$i
done