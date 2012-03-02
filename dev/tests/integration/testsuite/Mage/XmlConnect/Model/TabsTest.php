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
 * @package     Magento_XmlConnect
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Xmlconnect
 */
class Mage_XmlConnect_Model_TabsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (Mage::registry('current_app') === null) {
            $application = Mage::getModel('Mage_XmlConnect_Model_Application')->setType(
                Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPHONE
            );
            Mage::register('current_app', $application);
        }
    }

    public function testGetRenderTabs()
    {
        $model = new Mage_XmlConnect_Model_Tabs(false);
        $tabs = $model->getRenderTabs();
        $this->assertInternalType('array', $tabs);
        $this->assertNotEmpty($tabs);
        foreach ($tabs as $tab) {
            $this->assertArrayHasKey('label', $tab);
            $this->assertArrayHasKey('image', $tab);
            $this->assertArrayHasKey('action', $tab);
            $this->assertNotEmpty($tab['label']);
            $this->assertNotEmpty($tab['image']);
            $this->assertStringMatchesFormat(
                'http://%s/media/skin/%s/%s/%s/%s/%s/Mage_XmlConnect/images/%s.png', $tab['image']
            );
            $this->assertNotEmpty($tab['action']);
        }
    }

    public function testGetRenderTabsJson()
    {
        $model = new Mage_XmlConnect_Model_Tabs('{"enabledTabs":[{"image":"images/tab_account.png"}]}');
        $tabs = $model->getRenderTabs();
        $this->assertInternalType('array', $tabs);
        $this->assertNotEmpty($tabs);
        foreach ($tabs as $tab) {
            $this->assertInternalType('object', $tab);
            $this->assertObjectHasAttribute('image', $tab);
            $this->assertEquals('images/tab_account.png', $tab->image);
        }
    }
}
