<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Calculation\Rule;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test setClassTypeFilter with correct Class Type
     *
     * @param $classType
     * @param $elementId
     * @param $expected
     *
     * @dataProvider setClassTypeFilterDataProvider
     */
    public function testSetClassTypeFilter($classType, $elementId, $expected)
    {
        $collection = $this->_objectManager->create(
            \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class
        );
        $collection->setClassTypeFilter($classType, $elementId);
        $this->assertMatchesRegularExpression($expected, (string)$collection->getSelect());
    }

    public static function setClassTypeFilterDataProvider()
    {
        return [
            [
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
                1,
                '/`?cd`?\.`?product_tax_class_id`? = [\S]{0,1}1[\S]{0,1}/',
            ],
            [
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER,
                1,
                '/`?cd`?\.`?customer_tax_class_id`? = [\S]{0,1}1[\S]{0,1}/'
            ]
        ];
    }

    /**
     * Test setClassTypeFilter with wrong Class Type
     *
     */
    public function testSetClassTypeFilterWithWrongType()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $collection = $this->_objectManager->create(
            \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class
        );
        $collection->setClassTypeFilter('WrongType', 1);
    }
}
