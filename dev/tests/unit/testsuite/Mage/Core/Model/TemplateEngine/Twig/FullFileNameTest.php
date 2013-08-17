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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_TemplateEngine_Twig_FullFileNameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $_prevErrorLevel;

    /**
     * @var bool
     */
    private $_prevFrameworkWarningEnabled;

    /**
     * @var bool
     */
    private $_prevFrameworkNoticeEnabled;

    /** 
     * @var PHPUnit_Framework_MockObject_MockObject Mage_Core_Model_App_State
     */
    private $_appStateMock;

    public function setUp()
    {
        // prevent PHPUnit from converting real code exceptions
        $this->_prevErrorLevel = error_reporting();
        error_reporting(0);
        $this->_prevFrameworkNoticeEnabled = PHPUnit_Framework_Error_Notice::$enabled;
        PHPUnit_Framework_Error_Notice::$enabled = false;
        $this->_prevFrameworkWarningEnabled = PHPUnit_Framework_Error_Warning::$enabled;
        PHPUnit_Framework_Error_Warning::$enabled = false;
        
        $this->_appStateMock = $this->getMockBuilder('Mage_Core_Model_App_State')
            ->disableOriginalConstructor()
            ->getMock();

        // set to return developer mode by default
        $this->_appStateMock
            ->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue(Mage_Core_Model_App_State::MODE_DEVELOPER));
    }

    public function tearDown()
    {
        error_reporting($this->_prevErrorLevel);
        PHPUnit_Framework_Error_Warning::$enabled = $this->_prevFrameworkWarningEnabled;
        PHPUnit_Framework_Error_Notice::$enabled = $this->_prevFrameworkNoticeEnabled;
    }

    public function testFileExistencePositive()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);
        
        $this->assertNotNull($loader->getSource(__FILE__));
    }

    /**
     * @expectedException Twig_Error_Loader
     */
    public function testFileExistenceNegative()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);
        $loader->getSource(__FILE__ . 'jnk');
    }

    public function testGetCacheKey()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);

        $keyActual = "SomeKey";
        $keyExpected = $loader->getCacheKey($keyActual);

        $this->assertEquals($keyActual, $keyExpected);
    }

    public function testExists()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);

        $exists = $loader->exists(__FILE__);
        $this->assertEquals($exists, true);
    }

    public function testExistsBadFile()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);

        $name = 'bad-file';
        $exists = $loader->exists($name);
        $this->assertEquals($exists, false);
    }

    public function testIsFreshPositive()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);

        $this->assertEquals(true, $loader->isFresh(__FILE__, PHP_INT_MAX));
        $this->assertEquals(false, $loader->isFresh(__FILE__, 0));
    }

    /**
     * @expectedException Twig_Error_Loader
     */
    public function testIsFreshNegative()
    {
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($this->_appStateMock);

        $this->assertEquals(false, $loader->isFresh('bad-file', 0));
    }

    public function testIsFreshAppModes() 
    {
        // set to return production mode
        $productionStateMock = $this->getMockBuilder('Mage_Core_Model_App_State')
            ->disableOriginalConstructor()
            ->getMock();
        $productionStateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue(Mage_Core_Model_App_State::MODE_PRODUCTION));
        $loader = new Mage_Core_Model_TemplateEngine_Twig_FullFileName($productionStateMock);

        // in production mode, even a bad file will return as fresh
        $this->assertEquals(true, $loader->isFresh('bad-file', 0));
    }
}