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
 * @package     Magento_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Block\Adminhtml\Subscription\Edit;

/**
 * \Magento\Webhook\Block\Adminhtml\Subscription\Edit\Form
 *
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareForm()
    {
        /** @var $layout \Magento\Core\Model\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Layout');

        /** @var \Magento\Core\Model\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Registry');
        $subscription = array(
            'name' => 'subscriptionName',
            'endpoint_url' => 'example.url.com',
            'format' => 'JSON',
            'authentication_type' => 'manual',
            'topics' => 'customer/created',
            'subscription_id' => '4'
        );
        $registry
            ->register(
                'current_subscription',
                $subscription
            );

        /** @var \Magento\Webhook\Block\Adminhtml\Subscription\Edit\Form $block */
        $block = $layout->createBlock('Magento\Webhook\Block\Adminhtml\Subscription\Edit\Form',
            '', array('registry' => $registry)
        );
        $block->toHtml();

        $form = $block->getForm();

        $this->assertInstanceOf('Magento\Data\Form', $form);
        $this->assertEquals('post', $form->getData('method'));
        $this->assertEquals('edit_form', $form->getId());
        $this->assertTrue($form->getUseContainer());

        $expectedFieldset = array(
            'name' => array(
                'name' => 'name',
                'type' => 'text',
                'required' => true,
                'value' => $subscription['name']
            ),
            'endpoint_url' => array(
                'name' => 'endpoint_url',
                'type' => 'text',
                'required' => true,
                'value' => $subscription['endpoint_url']
            ),
            'format' => array(
                'name' => 'format',
                'type' => 'select',
                'required' => false,
                'value' => $subscription['format']
            ),
            'authentication_type' => array(
                'name' => 'authentication_type',
                'type' => 'select',
                'required' => false,
                'value' => $subscription['authentication_type']
            ),
            'topics' => array(
                'name' => 'topics[]',
                'type' => 'select',
                'required' => true,
                'value' => $subscription['topics']
            ),
        );

        foreach ($expectedFieldset as $fieldId => $field) {
            $element = $form->getElement($fieldId);
            $this->assertInstanceOf('Magento\Data\Form\Element\AbstractElement', $element);
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
