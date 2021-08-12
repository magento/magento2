<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\TestFunctions;

/* Test Functions */

use stdClass;

/**
 * Test Function
 *
 * @param string $arg
 * @return string
 */
function TestFunc($who)
{
    return "Hello $who";
}

/**
 * Test Function 2
 */
function TestFunc2()
{
    return "Hello World";
}

/**
 * Return false
 *
 * @return bool
 */
function TestFunc3()
{
    return false;
}

/**
 * Return true
 *
 * @return bool
 */
function TestFunc4()
{
    return true;
}

/**
 * Return integer
 *
 * @return int
 */
function TestFunc5()
{
    return 123;
}

/**
 * Return string
 *
 * @return string
 */
function TestFunc6()
{
    return "string";
}

/**
 * Return array
 *
 * @return array
 */
function TestFunc7()
{
    return ['foo' => 'bar', 'baz' => true, 1 => false, 'bat' => 123];
}

/**
 * Return Object
 *
 * @return stdClass
 */
function TestFunc8()
{
    $return = (object) ['foo' => 'bar', 'baz' => true, 'bat' => 123, 'qux' => false];
    return $return;
}

/**
 * Multiple Args
 *
 * @param string $foo
 * @param string $bar
 * @return string
 */
function TestFunc9($foo, $bar)
{
    return "$foo $bar";
}

/**
 * @param string $message
 */
function OneWay($message)
{

}

