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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\System\Account\Edit;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareForm()
    {
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\User'
        )->loadByUsername(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME
        );

        /** @var $session \Magento\Backend\Model\Auth\Session */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Auth\Session'
        );
        $session->setUser($user);

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );

        /** @var \Magento\Backend\Block\System\Account\Edit\Form */
        $block = $layout->createBlock('Magento\Backend\Block\System\Account\Edit\Form');
        $block->toHtml();

        $form = $block->getForm();

        $this->assertInstanceOf('Magento\Framework\Data\Form', $form);
        $this->assertEquals('post', $form->getData('method'));
        $this->assertEquals($block->getUrl('adminhtml/system_account/save'), $form->getData('action'));
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
            'password' => array('name' => 'password', 'type' => 'password', 'required' => false),
            'confirmation' => array('name' => 'password_confirmation', 'type' => 'password', 'required' => false),
            'interface_locale' => array('name' => 'interface_locale', 'type' => 'select', 'required' => false)
        );

        foreach ($expectedFieldset as $fieldId => $field) {
            $element = $form->getElement($fieldId);
            $this->assertInstanceOf('Magento\Framework\Data\Form\Element\AbstractElement', $element);
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
