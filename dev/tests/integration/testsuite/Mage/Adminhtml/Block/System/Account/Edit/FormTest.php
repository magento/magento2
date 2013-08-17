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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Adminhtml_Block_System_Account_Edit_FormTest extends PHPUnit_Framework_TestCase
{
    public function testPrepareForm()
    {
        $user = Mage::getModel('Mage_User_Model_User')->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);

        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        $session->setUser($user);

        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');

        /** @var Mage_Adminhtml_Block_System_Account_Edit_Form */
        $block = $layout->createBlock('Mage_Adminhtml_Block_System_Account_Edit_Form');
        $block->toHtml();

        $form = $block->getForm();

        $this->assertInstanceOf('Varien_Data_Form', $form);
        $this->assertEquals('post', $form->getData('method'));
        $this->assertEquals($block->getUrl('*/system_account/save'), $form->getData('action'));
        $this->assertEquals('edit_form', $form->getId());
        $this->assertTrue($form->getUseContainer());

        $expectedFieldset = array(
            'username' => array(
                'name' => 'username',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('username')
            ),
            'firstname' => array(
                'name' => 'firstname',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('firstname')
            ),
            'lastname' => array(
                'name' => 'lastname',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('lastname')
            ),
            'email' => array(
                'name' => 'email',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('email')
            ),
            'password' => array(
                'name' => 'password',
                'type' => 'password',
                'required' => false
            ),
            'confirmation' => array(
                'name' => 'password_confirmation',
                'type' => 'password',
                'required' => false
            ),
            'interface_locale' => array(
                'name' => 'interface_locale',
                'type' => 'select',
                'required' => false
            ),
        );

        foreach ($expectedFieldset as $fieldId => $field) {
            $element = $form->getElement($fieldId);
            $this->assertInstanceOf('Varien_Data_Form_Element_Abstract', $element);
            $this->assertEquals($field['name'], $element->getName(), 'Wrong \'' . $fieldId . '\' field name');
            $this->assertEquals($field['type'], $element->getType(), 'Wrong \'' . $fieldId . ' field type');
            $this->assertEquals(
                $field['required'],
                $element->getData('required'),
                'Wrong \'' . $fieldId . '\' requirement state'
            );
            if (array_key_exists('value', $field)) {
                $this->assertEquals($field['value'], $element->getData('value'), 'Wrong \'' . $fieldId . '\' value');
            }
        }
    }
}
