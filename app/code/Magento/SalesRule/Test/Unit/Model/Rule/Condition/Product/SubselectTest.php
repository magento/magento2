<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Rule\Model\Condition\Context;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect as SalesRuleProductSubselect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for checking sub select validation
 */
class SubselectTest extends TestCase
{
    /** @var SalesRuleProductSubselect */
    private $model;

    /** @var SalesRuleProduct|MockObject */
    private $ruleConditionMock;

    /** @var MockObject */
    private $abstractModel;

    /** @var Product|MockObject */
    private $productMock;

    /** @var Quote|MockObject */
    private $quoteMock;

    /** @var Item|MockObject */
    private $quoteItemMock;

    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleConditionMock = $this->getMockBuilder(SalesRuleProduct::class)
            ->onlyMethods(['getAttribute', 'getValueParsed', 'getOperatorForValidate'])
            ->addMethods(['setName', 'setAttributeScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractModel = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuote', 'getProduct'])
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getData', 'getResource', 'hasData'])
            ->addMethods(['getOperatorForValidate', 'getValueParsed'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllVisibleItems'])
            ->getMock();
        $this->quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getHasChildren', 'getProductId'])
            ->onlyMethods(
                [
                    'getData',
                    'getProduct',
                    'getProductType',
                    'getChildren',
                    'getQuote',
                    'getAddress',
                    'getOptionByCode'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn([$this->quoteItemMock]);
        $this->abstractModel->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->abstractModel->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->model = new SalesRuleProductSubselect(
            $contextMock,
            $this->ruleConditionMock,
            []
        );
    }

    /**
     * Tests validate for fixed bundle product
     *
     * @param array|null $attributeDetails
     * @param array $productDetails
     * @param bool $expectedResult
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider dataProviderForFixedBundleProduct
     */
    public function testValidateForFixedBundleProduct(
        ?array $attributeDetails,
        array $productDetails,
        bool $expectedResult
    ): void {
        $attributeResource = new DataObject();
        if ($attributeDetails) {
            $attributeResource->setAttribute($attributeDetails['id']);
            $this->ruleConditionMock->expects($this->any())
                ->method('setName')
                ->willReturn($attributeDetails['name']);
            $this->ruleConditionMock->expects($this->any())
                ->method('setAttributeScope')
                ->willReturn($attributeDetails['attributeScope']);
            $this->ruleConditionMock->expects($this->any())
                ->method('getAttribute')
                ->willReturn($attributeDetails['id']);
            $this->model->setData('conditions', [$this->ruleConditionMock]);
            $this->model->setData('attribute', $attributeDetails['id']);
            $this->model->setData('value', $productDetails['valueParsed']);
            $this->model->setData('operator', $attributeDetails['attributeOperator']);
            $this->productMock->expects($this->any())
                ->method('hasData')
                ->with($attributeDetails['id'])
                ->willReturn(!empty($productDetails));
            $this->productMock->expects($this->any())
                ->method('getData')
                ->with($attributeDetails['id'])
                ->willReturn($productDetails['price']);
            $this->ruleConditionMock->expects($this->any())
                ->method('getValueParsed')
                ->willReturn($productDetails['valueParsed']);
            $this->ruleConditionMock->expects($this->any())->method('getOperatorForValidate')
                ->willReturn($attributeDetails['attributeOperator']);
        }
        $this->quoteItemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn($productDetails['type']);

        /* @var AbstractItem|MockObject $quoteItemMock */
        $childQuoteItemMock = $this->getMockBuilder(AbstractItem::class)
            ->onlyMethods(['getProduct', 'getData', 'getPrice', 'getQty'])
            ->addMethods(['getBaseRowTotal'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $childQuoteItemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $childQuoteItemMock->expects($this->any())
            ->method('getQty')
            ->willReturn($productDetails['qty']);
        $childQuoteItemMock->expects($this->any())
            ->method('getPrice')
            ->willReturn($productDetails['price']);
        $childQuoteItemMock->expects($this->any())
            ->method('getBaseRowTotal')
            ->willReturn($productDetails['baseRowTotal']);
        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($attributeResource);
        $this->quoteItemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->quoteItemMock->expects($this->any())
            ->method('getHasChildren')
            ->willReturn($productDetails['hasChildren']);
        $this->quoteItemMock->expects($this->any())
            ->method('getChildren')
            ->willReturn([$childQuoteItemMock]);
        $this->quoteItemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productDetails['id']);
        $this->quoteItemMock->expects($this->any())
            ->method('getChildren')
            ->willReturn([$childQuoteItemMock]);
        $this->quoteItemMock->expects($this->any())
            ->method('getData')
            ->willReturn($productDetails['baseRowTotal']);
        $this->assertEquals($expectedResult, $this->model->validate($this->abstractModel));
    }

    /**
     * Get data provider array for validate bundle product
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderForFixedBundleProduct(): array
    {
        return [
            'validate true for bundle product data with conditions' =>
                [
                    [
                        'id' => 'attribute_set_id',
                        'name' => 'test conditions',
                        'attributeScope' => 'frontend',
                        'attributeOperator' => '=='
                    ],
                    [
                        'id'=> 1,
                        'type' => ProductType::TYPE_BUNDLE,
                        'qty' => 1,
                        'price' => 100,
                        'hasChildren' => true,
                        'baseRowTotal' => 100,
                        'valueParsed' => 100
                    ],
                    true
                ],
            'validate false for bundle product data with conditions' =>
                [
                    [
                        'id' => 'attribute_set_id',
                        'name' => 'test conditions',
                        'attributeScope' => 'frontend',
                        'attributeOperator' => '=='
                    ],
                    [
                        'id'=> 1,
                        'type' => ProductType::TYPE_BUNDLE,
                        'qty' => 1,
                        'price' => 100,
                        'hasChildren' => true ,
                        'baseRowTotal' => 100,
                        'valueParsed' => 50
                    ],
                    false
                ],
            'validate product data without conditions with bundle product' =>
                [
                    null,
                    [
                        'id'=> 1,
                        'type' => ProductType::TYPE_BUNDLE,
                        'qty' => 1,
                        'price' => 100,
                        'hasChildren' => true ,
                        'baseRowTotal' => 100,
                        'valueParsed' => 100
                    ],
                    false
                ],
            'validate true for bundle product data with conditions for attribute base_row_total' =>
                [
                    [
                        'id' => 'attribute_set_id',
                        'name' => 'base_row_total',
                        'attributeScope' => 'frontend',
                        'attributeOperator' => '=='
                    ],
                    [
                        'id'=> 1,
                        'type' => ProductType::TYPE_BUNDLE,
                        'qty' => 2,
                        'price' => 100,
                        'hasChildren' => true,
                        'baseRowTotal' => 200,
                        'valueParsed' => 200
                    ],
                    false
                ],
            'validate true for simple product data with conditions' =>
                [
                    [
                        'id' => 'attribute_set_id',
                        'name' => 'test conditions',
                        'attributeScope' => 'frontend',
                        'attributeOperator' => '=='
                    ],
                    [
                        'id'=> 1,
                        'type' => ProductType::TYPE_SIMPLE,
                        'qty' => 1,
                        'price' => 100,
                        'hasChildren' => false ,
                        'baseRowTotal' => 100,
                        'valueParsed' => 100
                    ],
                    true
                ],
            'validate false for simple product data with conditions' =>
                [
                    [
                        'id' => 'attribute_set_id',
                        'name' => 'test conditions',
                        'attributeScope' => 'frontend',
                        'attributeOperator' => '=='
                    ],
                    [
                        'id'=> 1,
                        'type' => ProductType::TYPE_SIMPLE,
                        'qty' => 1,
                        'price' => 100,
                        'hasChildren' => false,
                        'baseRowTotal' => 100,
                        'valueParsed' => 50
                    ],
                    false
                ]
        ];
    }
}
