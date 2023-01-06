<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FoundTest extends TestCase
{
    /**
     * @var Found
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
            ->setMethods(['loadAttributeOptions', 'getAttributeOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Found(
            $contextMock,
            $this->ruleConditionMock,
            []
        );
    }

    /**
     * @return void
     */
    public function testValidate()
    {
        $itemMock = $this->createMock(Item::class);
        $modelMock = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(['getProductId', 'setQty', 'setNote'])
            ->onlyMethods(['getId', 'getEntityId', 'save', 'delete', 'isDeleted'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $itemMock->expects($this->any())
            ->method('getEventPrefix')
            ->willReturn('sales_quote_item');
        $itemMock->expects($this->exactly(1))->method('load')->willReturn($modelMock);
        $result = $this->model->validate($itemMock);
        $this->assertTrue($result);

        $addressMock = $this->createMock(Address::class);
        $addressMock->expects($this->any())
            ->method('getEventPrefix')
            ->willReturn('sales_quote_address');
        $addressMock->expects($this->exactly(1))->method('getAllItems')->willReturn([]);
        $result = $this->model->validate($addressMock);
        $this->assertFalse($result);
    }
}