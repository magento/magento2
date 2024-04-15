<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Model\Rule\Condition\Combine;
use Magento\CatalogWidget\Model\Rule\Condition\Product;
use Magento\CatalogWidget\Model\Rule\Condition\ProductFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CombineTest extends TestCase
{
    /**
     * @var Combine|MockObject
     */
    protected $condition;

    /**
     * @var ProductFactory|MockObject
     */
    protected $conditionFactory;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            Combine::class
        );

        $this->conditionFactory = $this->getMockBuilder(
            ProductFactory::class
        )->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['conditionFactory'] = $this->conditionFactory;
        $arguments['excludedAttributes'] = ['excluded_attribute'];

        $this->condition = $objectManagerHelper->getObject(
            Combine::class,
            $arguments
        );
    }

    public function testGetNewChildSelectOptions()
    {
        $expectedOptions = [
            ['value' => '', 'label' => __('Please choose a condition to add.')],
            ['value' => Combine::class,
                'label' => __('Conditions Combination')],
            ['label' => __('Product Attribute'), 'value' => [
                ['value' => 'Magento\CatalogWidget\Model\Rule\Condition\Product|sku', 'label' => 'SKU'],
                ['value' => 'Magento\CatalogWidget\Model\Rule\Condition\Product|category', 'label' => 'Category'],
            ]],
        ];

        $attributeOptions = [
            'sku' => 'SKU',
            'category' => 'Category',
            'excluded_attribute' => 'Excluded attribute',
        ];
        $productCondition = $this->getMockBuilder(Product::class)
            ->addMethods(['getAttributeOption'])
            ->onlyMethods(['loadAttributeOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCondition->expects($this->any())->method('loadAttributeOptions')->willReturnSelf();
        $productCondition->expects($this->any())->method('getAttributeOption')
            ->willReturn($attributeOptions);

        $this->conditionFactory->expects($this->atLeastOnce())->method('create')->willReturn($productCondition);

        $this->assertEquals($expectedOptions, $this->condition->getNewChildSelectOptions());
    }

    public function testCollectValidatedAttributes()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $condition = $this->getMockBuilder(Combine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['collectValidatedAttributes'])
            ->getMock();
        $condition->expects($this->any())->method('collectValidatedAttributes')->with($collection)->willReturnSelf();

        $this->condition->setConditions([$condition]);

        $this->assertSame($this->condition, $this->condition->collectValidatedAttributes($collection));
    }
}
