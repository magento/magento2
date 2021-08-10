<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * Test Class
 */
class ServerTestClass
{
    /**
     * Test Function 1
     *
     * @return string
     */
    public function testFunc1()
    {
        return "Hello World";
    }

    /**
     * Test Function 2
     *
     * @param string $who Some Arg
     * @return string
     */
    public function testFunc2($who)
    {
        return "Hello $who!";
    }

    /**
     * Test Function 3
     *
     * @param string $who Some Arg
     * @param int $when Some
     * @return string
     */
    public function testFunc3($who, $when)
    {
        return "Hello $who, How are you $when";
    }

    /**
     * Test Function 4
     *
     * @return string
     */
    public static function testFunc4()
    {
        return "I'm Static!";
    }

    /**
     * Test Function 5 raises a user error
     *
     * @return void
     */
    public function testFunc5()
    {
        trigger_error("Test Message", E_USER_ERROR);
    }
}

