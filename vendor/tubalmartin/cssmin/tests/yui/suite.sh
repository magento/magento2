#!/usr/bin/env bash

cd $(dirname $0)
	
# Get the jar to use.
jar="$(ls ../build/*.jar | sort | tail -n1)"
echo "jar: $jar"

runtest () {
	testfile="$1"
	expected=${testfile/\.FAIL/}.min
	expected="$(
		cat $expected
	)"
	filetype="$(
		echo $testfile | egrep -o '(cs|j)s'
	)"
	
	actual="$(
	    java -jar $jar --type $filetype $testfile
	)"
	
	if [ "$expected" == "$actual" ]; then
		echo "Passed: $testfile" > /dev/stderr
	else
		(
			echo "Test failed: $testfile"
			echo ""
			echo "Expected:"
			echo "$expected"
			echo ""
			echo "Actual:"
			echo "$actual"
		) > /dev/stderr
		return 1
	fi
}


ls *.FAIL 2>/dev/null | while read failtest; do
	echo "Failing test: " $failtest > /dev/stderr
	runtest $failtest && echo "Test passed, please remove the '.FAIL' from the filename"
done

ls *.{css,js} | while read testfile; do
	runtest $testfile || exit 1
done

exit 0
