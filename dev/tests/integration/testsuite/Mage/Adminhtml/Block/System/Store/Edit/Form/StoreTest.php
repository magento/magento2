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

/**
 * @magentoAppIsolation enabled
 */
class Mage_Adminhtml_Block_System_Store_Edit_Form_StoreTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Adminhtml_Block_System_Store_Edit_Form_Store */
    protected $_block;

    public static function setUpBeforeClass()
    {
        $registryData = array(
            'store_type' => 'store',
            'store_data' => Mage::getModel('Mage_Core_Model_Store'),
            'store_action' => 'add'
        );
        foreach ($registryData as $key => $value) {
            Mage::register($key, $value);
        }
    }

    public static function tearDownAfterClass()
    {
        Mage::unregister('store_type');
        Mage::unregister('store_data');
        Mage::unregister('store_action');
    }

    public function setUp()
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');

        $this->_block = $layout->createBlock('Mage_Adminhtml_Block_System_Store_Edit_Form_Store');

        $this->_block->toHtml();
    }

    public function testPrepareForm()
    {
        $form = $this->_block->getForm();
        $this->assertEquals('store_fieldset', $form->getElement('store_fieldset')->getId());
        $this->assertEquals('store_name', $form->getElement('store_name')->getId());
        $this->assertEquals('store', $form->getElement('store_type')->getValue());
    }
}
