<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Workaround\Cleanup\TestCasePropertiesTest;

class DummyTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    public $testPublic;

    /**
     * @var string
     */
    private $_testPrivate;

    /**
     * @var boolean
     */
    protected $_testPropertyBoolean;

    /**
     * @var integer
     */
    protected $_testPropertyInteger;

    /**
     * @var float
     */
    protected $_testPropertyFloat;

    /**
     * @var string
     */
    protected $_testPropertyString;

    /**
     * @var array
     */
    protected $_testPropertyArray;

    /**
     * @var object
     */
    protected $_testPropertyObject;

    /**
     * @var string
     */
    public static $testPublicStatic;

    /**
     * @var string
     */
    protected static $_testProtectedStatic;

    /**
     * @var string
     */
    private static $_testPrivateStatic;

    public function testDummy()
    {
        $this->testPublic = 'public';
        $this->_testPrivate = 'private';
        $this->_testPropertyBoolean = true;
        $this->_testPropertyInteger = 10;
        $this->_testPropertyFloat = 1.97;
        $this->_testPropertyString = 'string';
        $this->_testPropertyArray = ['test', 20];
        self::$testPublicStatic = 'static public';
        self::$_testProtectedStatic = 'static protected';
        self::$_testPrivateStatic = 'static private';
    }

    /**
     * Assign value to the object property
     *
     * @param object $object
     */
    public function setPropertyObject($object)
    {
        $this->_testPropertyObject = $object;
    }
}
