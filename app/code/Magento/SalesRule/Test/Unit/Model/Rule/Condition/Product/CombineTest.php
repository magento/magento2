<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition\Product;

use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CombineTest extends TestCase
{
    /**
     * @var Combine
     */
    private $model;

    /**
     * @var SalesRuleProduct|MockObject
     */
    private $ruleConditionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleConditionMock = $this->getMockBuilder(SalesRuleProduct::class)
            ->onlyMethods(['loadAttributeOptions'])
            ->addMethods(['getAttributeOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Combine(
            $contextMock,
            $this->ruleConditionMock,
            []
        );
    }

    /**
     * @return void
     */
    public function testGetNewChildSelectOptions()
    {
        $this->ruleConditionMock->expects($this->any())
            ->method('loadAttributeOptions')
            ->willReturn($this->ruleConditionMock);
        $this->ruleConditionMock->expects($this->any())
            ->method('getAttributeOption')
            ->willReturn([
                'parent::quote_item_qty' => __('Quantity in cart'),
                'name' => __('Name')
            ]);
        $this->assertEquals([
            [
                'value' => '',
                'label' => __('Please choose a condition to add.')],
            [
                'value' => Combine::class,
                'label' => __('Conditions Combination'),
            ],
            [
                'label' => __('Cart Item Attribute'),
                'value' => [
                    [
                        'value' => SalesRuleProduct::class . '|' . 'parent::quote_item_qty',
                        'label' => __('Quantity in cart'),
                    ]
                ]
            ],
            [
                'label' => __('Product Attribute'),
                'value' => [
                    [
                        'value' => SalesRuleProduct::class . '|' . 'name',
                        'label' => __('Name'),
                    ]
                ]
            ]
        ], $this->model->getNewChildSelectOptions());
    }
}
