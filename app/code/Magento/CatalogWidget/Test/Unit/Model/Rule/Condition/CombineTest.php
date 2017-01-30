<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Test\Unit\Model\Rule\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CombineTest
 */
class CombineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\Combine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $condition;

    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $conditionFactory;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\CatalogWidget\Model\Rule\Condition\Combine'
        );

        $this->conditionFactory = $this->getMockBuilder('\Magento\CatalogWidget\Model\Rule\Condition\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments['conditionFactory'] = $this->conditionFactory;

        $this->condition = $objectManagerHelper->getObject(
            'Magento\CatalogWidget\Model\Rule\Condition\Combine',
            $arguments
        );
    }

    public function testGetNewChildSelectOptions()
    {
        $expectedOptions = [
            ['value' => '', 'label' => __('Please choose a condition to add.')],
            ['value' => 'Magento\CatalogWidget\Model\Rule\Condition\Combine', 'label' => __('Conditions Combination')],
            ['label' => __('Product Attribute'), 'value' => [
                ['value' => 'Magento\CatalogWidget\Model\Rule\Condition\Product|sku', 'label' => 'SKU'],
                ['value' => 'Magento\CatalogWidget\Model\Rule\Condition\Product|category', 'label' => 'Category'],
            ]],
        ];

        $attributeOptions = [
            'sku' => 'SKU',
            'category' => 'Category',
        ];
        $productCondition = $this->getMockBuilder('\Magento\CatalogWidget\Model\Rule\Condition\Product')
            ->setMethods(['loadAttributeOptions', 'getAttributeOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCondition->expects($this->any())->method('loadAttributeOptions')->will($this->returnSelf());
        $productCondition->expects($this->any())->method('getAttributeOption')
            ->will($this->returnValue($attributeOptions));

        $this->conditionFactory->expects($this->atLeastOnce())->method('create')->willReturn($productCondition);

        $this->assertEquals($expectedOptions, $this->condition->getNewChildSelectOptions());
    }

    public function testCollectValidatedAttributes()
    {
        $collection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $condition = $this->getMockBuilder('Magento\CatalogWidget\Model\Rule\Condition\Combine')
            ->disableOriginalConstructor()->setMethods(['addToCollection'])
            ->getMock();
        $condition->expects($this->any())->method('addToCollection')->with($collection)
            ->will($this->returnSelf());

        $this->condition->setConditions([$condition]);

        $this->assertSame($this->condition, $this->condition->collectValidatedAttributes($collection));
    }
}
