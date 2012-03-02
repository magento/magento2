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
 * @package     Mage_XmlConnect
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Xmlconnect
 */
class Mage_XmlConnect_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_XmlConnect_Helper_Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new Mage_XmlConnect_Helper_Data();
        Mage::getDesign()->setDesignTheme('default/default/default', 'adminhtml');
    }

    /**
     * @dataProvider getDefaultDesignTabsDataProvider
     */
    public function testGetDefaultDesignTabs($appType)
    {
        $application = new Mage_XmlConnect_Model_Application();
        $application->setType($appType);
        $tabs = $this->_helper->getDeviceHelper($application)->getDefaultDesignTabs();
        $this->assertNotEmpty($tabs);
        foreach ($tabs as $tab) {
            $this->assertArrayHasKey('image', $tab);
        }
    }

    public function getDefaultDesignTabsDataProvider()
    {
        return array(
            array(Mage_XmlConnect_Helper_Data::DEVICE_TYPE_ANDROID),
            array(Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPAD),
            array(Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPHONE)
        );
    }
}
