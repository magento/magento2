#!/bin/bash
# PHP CodeSniffer pre-commit hook for git
#
 
PHPCS_BIN=/usr/bin/phpcs
PHPCS_CODING_STANDARD=PSR2
PHPCS_MEMORY_LIMIT=1024M
PHPCS_IGNORE=
PHPCS_EXTENSIONS=php,phtml
PHPCS_ENCODING=utf-8
TMP_STAGING=".tmp"
 
# simple check if code sniffer is set up correctly
if [ ! -x $PHPCS_BIN ]; then
    echo "PHP Code Sniffer is not available: $PHPCS_BIN"
    exit 1
fi

# if HEAD is available for comparison, otherwise use an empty tree object
if git rev-parse --verify HEAD
then
    against=HEAD
else
    # Initial commit: diff against an empty tree object
    against=4b825dc642cb6eb9a060e54bf8d69288fbee4904
fi

FILES=$(git diff-index --name-only --cached --diff-filter=ACMR $against -- )
 
if [ "$FILES" == "" ]; then
    exit 0
fi

# create temporary copy of staging area
if [ -e $TMP_STAGING ]; then
    rm -rf $TMP_STAGING
fi
mkdir $TMP_STAGING

# match files against whitelist
FILES_TO_CHECK=""
for FILE in $FILES
do
    echo "$FILE" | egrep -q "$PHPCS_FILE_PATTERN"
    RETVAL=$?
    if [ "$RETVAL" -eq "0" ]
    then
        FILES_TO_CHECK="$FILES_TO_CHECK $FILE"
    fi
done
 
if [ "$FILES_TO_CHECK" == "" ]; then
    exit 0
fi
# execute the code sniffer
if [ "$PHPCS_IGNORE" != "" ]; then
    IGNORE="--ignore=$PHPCS_IGNORE"
else
    IGNORE=""
fi
 
if [ "$PHPCS_ENCODING" != "" ]; then
    ENCODING="--encoding=$PHPCS_ENCODING"
else
    ENCODING=""
fi
 
if [ "$PHPCS_IGNORE_WARNINGS" == "1" ]; then
    IGNORE_WARNINGS="-n"
else
    IGNORE_WARNINGS=""
fi

# Copy staged files to a processing folder
STAGED_FILES=""

for FILE in $FILES_TO_CHECK
do
  ID=`git diff-index --cached HEAD -- $FILE | cut -d " " -f4`

  # create staged version of file in temporary staging area with the same
  # path as the original file so that the phpcs ignore filters can be applied
  mkdir -p "$TMP_STAGING/$(dirname $FILE)"

  git cat-file blob $ID > "$TMP_STAGING/$FILE"
  STAGED_FILES="$STAGED_FILES $TMP_STAGING/$FILE"

done
 
OUTPUT=$($PHPCS_BIN -n -s $IGNORE_WARNINGS  --standard=$PHPCS_CODING_STANDARD -d memory_limit=$PHPCS_MEMORY_LIMIT $ENCODING --extensions=$PHPCS_EXTENSIONS $IGNORE $STAGED_FILES)
RETVAL=$?

# delete temporary copy of staging area
rm -rf $TMP_STAGING
 
if [ $RETVAL -ne 0 ]; then
    echo "Error Committing Files to Repository.  PHP Code Sniffer errors detected:"
    echo ""
    echo ""
    echo "$OUTPUT"
fi

exit $RETVAL
