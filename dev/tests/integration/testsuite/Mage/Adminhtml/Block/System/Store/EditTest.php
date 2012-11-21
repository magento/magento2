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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_System_Store_EditTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mage::unregister('store_type');
        Mage::unregister('store_data');
        Mage::unregister('store_action');
    }

    /**
     * @param $registryData
     */
    protected function _initStoreTypesInRegistry($registryData)
    {
        foreach ($registryData as $key => $value) {
            Mage::register($key, $value);
        }
    }

    /**
     * @param $registryData
     * @param $expected
     * @dataProvider getStoreTypesForLayout
     */
    public function testStoreTypeFormCreated($registryData, $expected)
    {
        $this->_initStoreTypesInRegistry($registryData);

        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Adminhtml_Block_System_Store_Edit */
        $block = $layout->createBlock('Mage_Adminhtml_Block_System_Store_Edit', 'block');
        $block->setArea(Mage_Core_Model_App_Area::AREA_ADMINHTML);

        $this->assertInstanceOf($expected, $block->getChildBlock('form'));
    }

    /**
     * @return array
     */
    public function getStoreTypesForLayout()
    {
        return array(
            array(
                array('store_type'=>'website', 'store_data'=> Mage::getModel('Mage_Core_Model_Website')),
                'Mage_Adminhtml_Block_System_Store_Edit_Form_Website'
            ),
            array(
                array('store_type'=>'group', 'store_data'=> Mage::getModel('Mage_Core_Model_Store_Group')),
                'Mage_Adminhtml_Block_System_Store_Edit_Form_Group'
            ),
            array(
                array('store_type'=>'store', 'store_data'=> Mage::getModel('Mage_Core_Model_Store')),
                'Mage_Adminhtml_Block_System_Store_Edit_Form_Store'
            )
        );
    }
    /**
     * @param $registryData
     * @param $expected
     * @dataProvider getStoreDataForBlock
     */
    public function testGetHeaderText($registryData, $expected)
    {
        $this->_initStoreTypesInRegistry($registryData);

        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Adminhtml_Block_System_Store_Edit */
        $block = $layout->createBlock('Mage_Adminhtml_Block_System_Store_Edit', 'block');
        $block->setArea(Mage_Core_Model_App_Area::AREA_ADMINHTML);

        $this->assertEquals($expected, $block->getHeaderText());
    }

    /**
     * @return array
     */
    public function getStoreDataForBlock()
    {
        return array(
            array(
                array(
                    'store_type' => 'website',
                    'store_data' => Mage::getModel('Mage_Core_Model_Website'),
                    'store_action' => 'add'
                ),
                'New Website'
            ),
            array(
                array(
                    'store_type' => 'website',
                    'store_data' => Mage::getModel('Mage_Core_Model_Website'),
                    'store_action' => 'edit'
                ),
                'Edit Website'
            ),
            array(
                array(
                    'store_type' => 'group',
                    'store_data' => Mage::getModel('Mage_Core_Model_Store_Group'),
                    'store_action' => 'add'
                ),
                'New Store'
            ),
            array(
                array(
                    'store_type' => 'group',
                    'store_data' => Mage::getModel('Mage_Core_Model_Store_Group'),
                    'store_action' => 'edit'
                ),
                'Edit Store'
            ),
            array(
                array(
                    'store_type' => 'store',
                    'store_data' => Mage::getModel('Mage_Core_Model_Store'),
                    'store_action' => 'add'
                ),
                'New Store View'
            ),
            array(
                array(
                    'store_type' => 'store',
                    'store_data' => Mage::getModel('Mage_Core_Model_Store'),
                    'store_action' => 'edit'
                ),
                'Edit Store View'
            )
        );
    }
}
