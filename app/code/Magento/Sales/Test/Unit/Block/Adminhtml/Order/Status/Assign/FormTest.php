<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Status\Assign;

use Magento\Sales\Block\Adminhtml\Order\Status\Assign\Form;

/**
 * Class FormTest
 * @package Magento\Sales\Block\Adminhtml\Order\Status\Assign
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Form
     */
    protected $block;

    /**
     * @var \Magento\Framework\Data\FormFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $formFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderConfig;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->formFactory = $this->createPartialMock(\Magento\Framework\Data\FormFactory::class, ['create']);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create']
        );
        $this->orderConfig = $this->createMock(\Magento\Sales\Model\Order\Config::class);

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

        $form = $this->createMock(\Magento\Framework\Data\Form::class);
        $fieldset = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Status\Collection::class);

        $form->expects($this->once())
            ->method('addFieldset')
            ->willReturn($fieldset);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $collection->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($statuses);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->orderConfig->expects($this->once())
            ->method('getStates')
            ->willReturn($states);

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
