<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Status\Assign;

use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Status\Assign\Form;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var Form
     */
    protected $block;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var Config|MockObject
     */
    protected $orderConfig;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->formFactory = $this->createPartialMock(FormFactory::class, ['create']);
        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->orderConfig = $this->createMock(Config::class);

        $this->block = $objectManager->getObject(
            Form::class,
            [
                'formFactory' => $this->formFactory,
                'collectionFactory' => $this->collectionFactory,
                'orderConfig' => $this->orderConfig,
                'data' => ['template' => null]
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testToHtml(): void
    {
        $statuses = ['status1', 'status2'];
        $states = ['state1', 'state2'];

        $statusesForField = $statuses;
        array_unshift($statusesForField, ['value' => '', 'label' => '']);
        $statesForField = array_merge(['' => ''], $states);

        $form = $this->createMock(\Magento\Framework\Data\Form::class);
        $fieldset = $this->createMock(Fieldset::class);
        $collection = $this->createMock(Collection::class);

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

        $fieldset->method('addField')
            ->willReturnCallback(
                // @phpstan-ignore-next-line
                function ($arg1, $arg2, $arg3) use ($statusesForField, $statesForField) {
                    if ($arg1 === 'status' && $arg2 === 'select' && $arg3['name'] === 'status') {
                        return null;
                    } elseif ($arg1 === 'state' && $arg2 === 'select' && $arg3['name'] === 'state') {
                        return null;
                    } elseif ($arg1 === 'is_default' && $arg2 === 'checkbox' && $arg3['name'] === 'is_default') {
                        return null;
                    } elseif ($arg1 === 'visible_on_front'
                        && $arg2 === 'checkbox'
                        && $arg3['name'] === 'visible_on_front'
                    ) {
                        return null;
                    }
                }
            );

        $this->block->toHtml();
    }
}
