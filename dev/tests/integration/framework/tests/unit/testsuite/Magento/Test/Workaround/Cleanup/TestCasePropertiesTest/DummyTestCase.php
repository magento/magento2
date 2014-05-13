<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_testPropertyArray = array('test', 20);
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
