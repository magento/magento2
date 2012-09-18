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
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Adminhtml
 */
class Mage_Adminhtml_Model_Config_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array $groups
     * @magentoDbIsolation enabled
     * @dataProvider saveDataProvider
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testSaveWithSingleStoreModeEnabled($groups)
    {
        $_configDataObject = new Mage_Adminhtml_Model_Config_Data();
        $_configData = $_configDataObject->setSection('dev')
            ->setWebsite('base')
            ->load();
        $this->assertEmpty($_configData);

        $_configDataObject = new Mage_Adminhtml_Model_Config_Data();
        $_configDataObject->setSection('dev')
            ->setGroups($groups)
            ->save();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        $_configDataObject = new Mage_Adminhtml_Model_Config_Data();
        $_configDataObject->setSection('dev')
            ->setWebsite('base');

        $_configData = $_configDataObject->load();
        $this->assertArrayHasKey('dev/debug/template_hints', $_configData);
        $this->assertArrayHasKey('dev/debug/template_hints_blocks', $_configData);

        $_configDataObject = new Mage_Adminhtml_Model_Config_Data();
        $_configDataObject->setSection('dev');
        $_configData = $_configDataObject->load();
        $this->assertArrayNotHasKey('dev/debug/template_hints', $_configData);
        $this->assertArrayNotHasKey('dev/debug/template_hints_blocks', $_configData);
    }

    public function saveDataProvider()
    {
        return require(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'config_groups.php');
    }
}
