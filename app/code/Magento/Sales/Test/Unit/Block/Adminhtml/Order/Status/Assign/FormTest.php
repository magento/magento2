<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Status\Assign;

use \Magento\Sales\Block\Adminhtml\Order\Status\Assign\Form;

/**
 * Class FormTest
 * @package Magento\Sales\Block\Adminhtml\Order\Status\Assign
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Form
     */
    protected $block;

    /**
     * @var \Magento\Framework\Data\FormFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->formFactory = $this->getMock(\Magento\Framework\Data\FormFactory::class, ['create'], [], '', false);
        $this->collectionFactory = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->orderConfig = $this->getMock(\Magento\Sales\Model\Order\Config::class, [], [], '', false);

        $this->block = $objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Status\Assign\Form::class,
            [
                'formFactory' => $this->formFactory,
                'collectionFactory' => $this->collectionFactory,
                'orderConfig' => $this->orderConfig,
                'data' => ['template' => null]
            ]
        );
    }

    public function testToHtml()
    {
        $statuses = ['status1', 'status2'];
        $states = ['state1', 'state2'];

        $statusesForField = $statuses;
        array_unshift($statusesForField, ['value' => '', 'label' => '']);
        $statesForField = array_merge(['' => ''], $states);

        $form = $this->getMock(\Magento\Framework\Data\Form::class, [], [], '', false);
        $fieldset = $this->getMock(\Magento\Framework\Data\Form\Element\Fieldset::class, [], [], '', false);
        $collection = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class,
            [],
            [],
            '',
            false
        );

        $form->expects($this->once())
            ->method('addFieldset')
            ->will($this->returnValue($fieldset));
        $this->formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($form));

        $collection->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue($statuses));
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));

        $this->orderConfig->expects($this->once())
            ->method('getStates')
            ->will($this->returnValue($states));

        $fieldset->expects($this->at(0))
            ->method('addField')
            ->with(
                'status',
                'select',
                [
                    'name' => 'status',
                    'label' => __('Order Status'),
                    'class' => 'required-entry',
                    'values' => $statusesForField,
                    'required' => true
                ]
            );
        $fieldset->expects($this->at(1))
            ->method('addField')
            ->with(
                'state',
                'select',
                [
                    'name' => 'state',
                    'label' => __('Order State'),
                    'class' => 'required-entry',
                    'values' => $statesForField,
                    'required' => true
                ]
            );
        $fieldset->expects($this->at(2))
            ->method('addField')
            ->with(
                'is_default',
                'checkbox',
                ['name' => 'is_default', 'label' => __('Use Order Status As Default'), 'value' => 1]
            );
        $fieldset->expects($this->at(3))
            ->method('addField')
            ->with(
                'visible_on_front',
                'checkbox',
                ['name' => 'visible_on_front', 'label' => __('Visible On Storefront'), 'value' => 1, 'checked' => true]
            );

        $this->block->toHtml();
    }
}
