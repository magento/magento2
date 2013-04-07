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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_App_AreaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_App_Area
     */
    protected $_model;

    public static function tearDownAfterClass()
    {
        Mage::app()->cleanCache(array(Mage_Core_Model_Design::CACHE_TAG));
    }

    public function setUp()
    {
        /** @var $_model Mage_Core_Model_App_Area */
        $this->_model = Mage::getModel('Mage_Core_Model_App_Area', array('areaCode' => 'frontend'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInitDesign()
    {
        $defaultTheme = Mage::getDesign()->setDefaultDesignTheme()->getDesignTheme();
        $this->_model->load(Mage_Core_Model_App_Area::PART_DESIGN);
        $design = Mage::getDesign()->setDefaultDesignTheme();

        $this->assertEquals($defaultTheme->getThemePath(), $design->getDesignTheme()->getThemePath());
        $this->assertEquals('frontend', $design->getArea());

        // try second time and make sure it won't load second time
        $this->_model->load(Mage_Core_Model_App_Area::PART_DESIGN);
        $this->assertSame($design, Mage::getDesign());
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoConfigFixture current_store design/theme/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:14:"default/modern";}}
     * @magentoAppIsolation enabled
     */
    // @codingStandardsIgnoreEnd
    public function testDetectDesignUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Firefox';
        $this->_model->detectDesign(new Zend_Controller_Request_Http);
        $this->assertEquals('default/modern', Mage::getDesign()->getDesignTheme()->getThemePath());
    }

    /**
     * @magentoDataFixture Mage/Core/_files/design_change.php
     * @magentoAppIsolation enabled
     */
    public function testDetectDesignDesignChange()
    {
        $this->_model->detectDesign();
        $this->assertEquals('default/modern', Mage::getDesign()->getDesignTheme()->getThemePath());
    }

    // @codingStandardsIgnoreStart
    /**
     * Test that non-frontend areas are not affected neither by user-agent reg expressions, nor by the "design change"
     *
     * @magentoConfigFixture current_store design/theme/ua_regexp a:1:{s:1:"_";a:2:{s:6:"regexp";s:10:"/firefox/i";s:5:"value";s:22:"default/demo_blue";}}
     * magentoDataFixture Mage/Core/_files/design_change.php
     * @magentoAppIsolation enabled
     */
    // @codingStandardsIgnoreEnd
    public function testDetectDesignNonFrontend()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Firefox';
        $model = Mage::getModel('Mage_Core_Model_App_Area', array('areaCode' => 'install'));
        $model->detectDesign(new Zend_Controller_Request_Http);
        $this->assertNotEquals('default/modern', Mage::getDesign()->getDesignTheme()->getThemePath());
        $this->assertNotEquals('default/demo_blue', Mage::getDesign()->getDesignTheme()->getThemePath());
    }
}
