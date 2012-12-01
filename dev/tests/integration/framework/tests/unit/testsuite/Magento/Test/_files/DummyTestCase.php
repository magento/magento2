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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Stub.php';

class Magento_Test_ClearProperties_DummyTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    public $testPublic;
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
     * @var mixed
     */
    protected $_testPropertyObject;

    /**
     * @var string
     */
    static public $testPublicStatic;
    static protected $_testProtectedStatic;
    static private $_testPrivateStatic;

    public function testDummy()
    {
        $this->testPublic = 'public';
        $this->_testPrivate = 'private';
        $this->_testPropertyBoolean = true;
        $this->_testPropertyInteger = 10;
        $this->_testPropertyFloat = 1.97;
        $this->_testPropertyString = 'string';
        $this->_testPropertyArray = array('test', 20);
        $this->_testPropertyObject = new Magento_Test_ClearProperties_Stub();
        self::$testPublicStatic = 'static public';
        self::$_testProtectedStatic = 'static protected';
        self::$_testPrivateStatic = 'static private';
    }
}
