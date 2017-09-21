<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Account\Edit;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    public function testPrepareForm()
    {
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\User\Model\User::class
        )->loadByUsername(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME
        );

        /** @var $session \Magento\Backend\Model\Auth\Session */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Auth\Session::class
        );
        $session->setUser($user);

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );

        /** @var \Magento\Backend\Block\System\Account\Edit\Form */
        $block = $layout->createBlock(\Magento\Backend\Block\System\Account\Edit\Form::class);
        $block->toHtml();

        $form = $block->getForm();

        $this->assertInstanceOf(\Magento\Framework\Data\Form::class, $form);
        $this->assertEquals('post', $form->getData('method'));
        $this->assertEquals($block->getUrl('adminhtml/system_account/save'), $form->getData('action'));
        $this->assertEquals('edit_form', $form->getId());
        $this->assertTrue($form->getUseContainer());

        $expectedFieldset = [
            'username' => [
                'name' => 'username',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('username'),
            ],
            'firstname' => [
                'name' => 'firstname',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('firstname'),
            ],
            'lastname' => [
                'name' => 'lastname',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('lastname'),
            ],
            'email' => [
                'name' => 'email',
                'type' => 'text',
                'required' => true,
                'value' => $user->getData('email'),
            ],
            'password' => ['name' => 'password', 'type' => 'password', 'required' => false],
            'confirmation' => ['name' => 'password_confirmation', 'type' => 'password', 'required' => false],
            'interface_locale' => ['name' => 'interface_locale', 'type' => 'select', 'required' => false],
        ];

        foreach ($expectedFieldset as $fieldId => $field) {
            $element = $form->getElement($fieldId);
            $this->assertInstanceOf(\Magento\Framework\Data\Form\Element\AbstractElement::class, $element);
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
