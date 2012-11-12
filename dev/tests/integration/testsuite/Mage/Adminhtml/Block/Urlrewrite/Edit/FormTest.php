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
 * Test for Mage_Adminhtml_Block_Urlrewrite_Edit_FormTest
 */
class Mage_Adminhtml_Block_Urlrewrite_Edit_FormTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get form instance
     *
     * @param array $args
     * @return Varien_Data_Form
     */
    protected function _getFormInstance($args = array())
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Adminhtml_Block_Urlrewrite_Edit_Form */
        $block = $layout->createBlock('Mage_Adminhtml_Block_Urlrewrite_Edit_Form', 'block', $args);
        $block->toHtml();
        return $block->getForm();
    }

    /**
     * Test that form was prepared correctly
     */
    public function testPrepareForm()
    {
        // Test form was configured correctly
        $form = $this->_getFormInstance(array('url_rewrite' => new Varien_Object(array('id' => 3))));
        $this->assertInstanceOf('Varien_Data_Form', $form);
        $this->assertNotEmpty($form->getAction());
        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());
        $this->assertTrue($form->getUseContainer());
        $this->assertContains('/id/3', $form->getAction());

        // Check all expected form elements are present
        $expectedElements = array(
            'is_system',
            'id_path',
            'request_path',
            'target_path',
            'options',
            'description',
            'store_id'
        );
        foreach ($expectedElements as $expectedElement) {
            $this->assertNotNull($form->getElement($expectedElement));
        }
    }

    /**
     * Check session data restoring
     */
    public function testSessionRestore()
    {
        // Set urlrewrite data to session
        $sessionValues = array(
            'store_id'     => 1,
            'id_path'      => 'id_path',
            'request_path' => 'request_path',
            'target_path'  => 'target_path',
            'options'      => 'options',
            'description'  => 'description'
        );
        Mage::getModel('Mage_Adminhtml_Model_Session')->setUrlrewriteData($sessionValues);
        // Re-init form to use newly set session data
        $form = $this->_getFormInstance(array('url_rewrite' => new Varien_Object()));

        // Check that all fields values are restored from session
        foreach ($sessionValues as $field => $value) {
            $this->assertEquals($value, $form->getElement($field)->getValue());
        }
    }

    /**
     * Test store element is hidden when only one store available
     *
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testStoreElementSingleStore()
    {
        $form = $this->_getFormInstance(array('url_rewrite' => new Varien_Object(array('id' => 3))));
        /** @var $storeElement Varien_Data_Form_Element_Abstract */
        $storeElement = $form->getElement('store_id');
        $this->assertInstanceOf('Varien_Data_Form_Element_Hidden', $storeElement);

        // Check that store value set correctly
        $defaultStore = Mage::app()->getStore(true)->getId();
        $this->assertEquals($defaultStore, $storeElement->getValue());
    }

    /**
     * Test store selection is available and correctly configured
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/store.php
     */
    public function testStoreElementMultiStores()
    {
        $form = $this->_getFormInstance(array('url_rewrite' => new Varien_Object(array('id' => 3))));
        /** @var $storeElement Varien_Data_Form_Element_Abstract */
        $storeElement = $form->getElement('store_id');

        // Check store selection elements has correct type
        $this->assertInstanceOf('Varien_Data_Form_Element_Select', $storeElement);

        // Check store selection elements has correct renderer
        $this->assertInstanceOf('Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset_Element',
            $storeElement->getRenderer());

        // Check store elements has expected values
        $storesList = Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm();
        $this->assertInternalType('array', $storeElement->getValues());
        $this->assertNotEmpty($storeElement->getValues());
        $this->assertEquals($storesList, $storeElement->getValues());
    }

    /**
     * Test fields disabled status
     * @dataProvider fieldsStateDataProvider
     */
    public function testDisabledFields($urlRewrite, $fields)
    {
        $form = $this->_getFormInstance(array('url_rewrite' => $urlRewrite));
        foreach ($fields as $fieldKey => $expected) {
            $this->assertEquals($expected, $form->getElement($fieldKey)->getDisabled());
        }
    }

    /**
     * Data provider for checking fields state
     */
    public function fieldsStateDataProvider()
    {
        return array(
            array(
                new Varien_Object(),
                array(
                    'is_system'    => true,
                    'id_path'      => false,
                    'request_path' => false,
                    'target_path'  => false,
                    'options'      => false,
                    'description'  => false
                )
            ),
            array(
                new Varien_Object(array('id' => 3)),
                array(
                    'is_system'    => true,
                    'id_path'      => false,
                    'request_path' => false,
                    'target_path'  => false,
                    'options'      => false,
                    'description'  => false
                )
            )
        );
    }
}
