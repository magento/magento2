<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

header('Content-Type: text/html;charset=utf-8');

/**
 * Prints a HTML heading
 */
function h($content, $size = 2) {
    printf('<h%d>'.htmlspecialchars($content).'</h%d>'."\n", $size, $size);
}

/**
 * Prints a HTML paragraph
 */
function p($content, $class) {
    printf('<p class="%s">'.htmlspecialchars($content).'</p>'."\n", $class);
}

/**
 * Prints a HTML code section
 */
function code($code) {
    printf('<pre><code>%s</code></pre>'."\n", $code);
}

/**
 * pTest - PHP Unit Tester
 * @param mixed $test Condition to test, evaluated as boolean
 * @param string $message Descriptive message to output upon test
 * @url http://www.sitepoint.com/blogs/2007/08/13/ptest-php-unit-tester-in-9-lines-of-code/
 */
function assertTrue($test, $message)
{
    static $count;
    if (!isset($count)) $count = array('pass'=>0, 'fail'=>0, 'total'=>0);

    $mode = $test ? 'pass' : 'fail';
    $outMode = $test ? 'PASS' : '!FAIL';
    p(sprintf("%s: %s (%d of %d tests run so far have %sed)\n",
        $outMode, $message, ++$count[$mode], ++$count['total'], $mode), $mode);

    return (bool)$test;
}

/**
 * Get number of bytes in a string regardless of mbstring.func_overload
 *
 * @param string $str
 * @return int
 */
function countBytes($str)
{
    return (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
        ? mb_strlen($str, '8bit')
        : strlen($str);
}

/**
 * Gets a file contents if the file exists
 * @param string $file
 * @return string|bool
 */
function get_expected($file)
{
    return file_exists($file) ? trim(file_get_contents($file)) : FALSE;
}

/**
 * Prints a test result
 */
function test($file, $minExpected, $skip)
{
    global $cssmin;

    if (! empty($skip) && in_array(basename($file), $skip)) {
        p("INFO: CSSmin: skipping " . basename($file), 'info');
        return;
    }

    $src = file_get_contents($file);
    $minOutput = $cssmin->run($src);

    $passed = assertTrue((strcmp($minOutput, $minExpected) === 0), 'CSSmin: ' . basename(dirname($file)) . '/' . basename($file));
    if (! $passed) {
        p("---Output: " .countBytes($minOutput). " bytes", '');
        $opcodes = FineDiff::getDiffOpcodes($minExpected, $minOutput);
        code(FineDiff::renderDiffToHTMLFromOpcodes($minExpected, $opcodes));
        p("---Expected: " .countBytes($minExpected). " bytes", '');
        code($minExpected);
        p("---Source: " .countBytes($src). " bytes", '');
        code($src);
    }
}

/**
 * Runs all test suites
 */
function run_tests()
{
?>
    <!DOCTYPE HTML>
    <html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <title>CSSmin TESTS</title>
        <style type="text/css">
            html, body{font: 12px 'Bitstream Vera Sans Mono','Courier', monospace;}
            pre {
                background-color: ghostWhite;
                white-space: pre-wrap; /* css-3 */
                white-space: -moz-pre-wrap !important; /* Mozilla, since 1999 */
                white-space: -pre-wrap; /* Opera 4-6 */
                white-space: -o-pre-wrap; /* Opera 7 */
                word-wrap: break-word; /* Internet Explorer 5.5+ */
            }
            ins{color:black;background-color:#DFD;text-decoration: none;}
            del{color: black;background-color:#FDD;text-decoration: none;}
            .pass{color:green}
            .fail{color:red}
            .info{color:blue}
        </style>
    </head>
    <body>
    <h1>YUI CSS compressor PHP - Test suites</h1>
<?php
    run_my_tests();
    run_yahoo_tests();
    //run_microsoft_tests();
?>
    </body>
    </html>
<?php
}

/**
 * Starts my test suite
 */
function run_my_tests()
{
    h("PHP PORT TESTS");

    $files = glob(dirname(__FILE__) . '/mine/*.css');
    $skip = array();

    foreach ($files as $file) {
        if ($expected = get_expected($file . '.min')) {
            test($file, $expected, $skip);
        }
    }
}

/**
 * Starts Yahoo!'s test suite
 */
function run_yahoo_tests()
{
    h("YAHOO! ORIGINAL TESTS");

    $files = glob(dirname(__FILE__) . '/yui/*.css');
    $skip = array();

    foreach ($files as $file) {
        if ($expected = get_expected($file . '.min')) {
            test($file, $expected, $skip);
        }
    }
}

/**
 * Starts Microsoft's test suite
 */
function run_microsoft_tests()
{
    h("MICROSOFT ORIGINAL TESTS");

    $files = glob(dirname(__FILE__) . '/microsoft-ajaxmin/Input/*/*.css');
    $skip = array(
        //'Media.css', // YUI lowercases the "AND", it's fine!
        //'Other.css', // YUI removes unnecessary semicolons so it's fine!
        //'CSS3.css', // YUI removes empty rules, it's fine!
        //'ParsingErrors.css', // YUI does not parse errors
        //'IEhacks.css', // YUI inserts ; if the * hack is used and it's the last property in a block so it's fine!
        //'HideFromMacIE.css', // YUI removes the space in /* \*/, but it's fine!
        //'ImportantCommentHacks.css', // YUI removes the space in /* \*/, but it's fine!
        //'Simple.css', // YUI minifies border:none; to border:0; so it's fine!
        //'Term.css', // My port removes the + sign of positive signed numbers, so it's fine!
        //'ValueReplacement.css' // It's not a CSS feature, see http://ajaxmin.codeplex.com/discussions/275960
    );

    foreach ($files as $file) {
        if ($expected = get_expected(str_replace('/Input/', '/Expected/', $file))) {
            test($file, $expected, $skip);
        }
    }
}



require_once 'finediff.php';
require_once '../cssmin.php';

$cssmin = new CSSmin();

run_tests();
