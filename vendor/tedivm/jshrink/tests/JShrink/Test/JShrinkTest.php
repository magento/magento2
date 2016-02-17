<?php

/*
 * This file is part of the JShrink package.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JShrink\Test;

use JShrink\Minifier;

class JShrinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unclosed multiline comment at position: 1
     */
    public function testUnclosedCommentException()
    {
        \JShrink\Minifier::minify('/* This comment is hanging out.');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unclosed string at position: 14
     */
    public function testUnclosedStringException()
    {
        \JShrink\Minifier::minify('var string = "This string is hanging out.');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unclosed regex pattern at position: 23
     */
    public function testUnclosedRegexException()
    {
        \JShrink\Minifier::minify('var re = /[^A-Za-z0-9_
        var string = "Another Filler"');
    }

    /**
     * @jshrink
     * @dataProvider JShrinkProvider
     */
    public function testJShrink($testName, $input, $output)
    {
        $this->assertEquals($output, \JShrink\Minifier::minify($input), 'Running JShrink Test: ' . $testName);
    }

    /**
     * @uglify
     * @dataProvider uglifyProvider
     */
    public function testUglify($testName, $input, $output)
    {
        $this->assertEquals($output, \JShrink\Minifier::minify($input), 'Running Uglify Test: ' . $testName);
    }

    /**
     * @group requests
     * @dataProvider requestProvider
     */
    public function testRequests($testName, $input, $output)
    {
        $this->assertEquals($output, \JShrink\Minifier::minify($input), 'Running User Requested Test: ' . $testName);
    }

    /**
     * @group development
     * @dataProvider developmentProvider
     */
    public function testDevelopment($testName, $input, $output)
    {
        $this->assertEquals($output, \JShrink\Minifier::minify($input), 'Running Development Test: ' . $testName);
    }

    /**
     * This function loads all of the test cases from the specified group.
     * Groups are created simply by populating the appropriate directories:
     *
     *    /tests/Resources/GROUPNAME/input/
     *    /tests/Resources/GROUPNAME/output/
     *
     * Each test case should have two identically named files, with the raw
     * javascript going in the test folder and the expected results to be in
     * the output folder.
     *
     * @param $group string
     * @return array
     */
    public function getTestFiles($group)
    {
        $baseDir = __DIR__ . '/../../Resources/' . $group . '/';
        $testDir = $baseDir . 'input/';
        $expectDir = $baseDir . 'output/';

        $returnData = array();

        $testFiles = scandir($testDir);
        foreach ($testFiles as $testFile) {
            if(substr($testFile, -3) !== '.js' || !file_exists(($expectDir . $testFile)))
                continue;

            $testInput = file_get_contents($testDir . $testFile);
            $testOutput = file_get_contents($expectDir . $testFile);

            $returnData[] = array($testFile, $testInput, $testOutput);
        }

        return $returnData;
    }

    public function uglifyProvider()
    {
        return $this->getTestFiles('uglify');
    }

    public function JShrinkProvider()
    {
        return $this->getTestFiles('jshrink');
    }

    public function requestProvider()
    {
        return $this->getTestFiles('requests');
    }

    public function developmentProvider()
    {
        return $this->getTestFiles('development');
    }
}
