<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\DataProvider\Price.
 *
 * @magentoAppIsolation enabled
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    protected $_model;

    protected function setUp(): void
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $category->load(4);
        $layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Model\Layer\Category::class);
        $layer->setCurrentCategory($category);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Layer\Filter\DataProvider\Price::class, ['layer' => $layer]);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation auto
     */
    public function testGetPriceRangeAuto()
    {
        $this->assertEquals(10, $this->_model->getPriceRange());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step 1.5
     */
    public function testGetPriceRangeManual()
    {
        // what you set is what you get
        $this->assertEquals(1.5, $this->_model->getPriceRange());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGetMaxPriceInt()
    {
        $this->assertEquals(45.00, $this->_model->getMaxPrice());
    }

    /**
     * @return array
     */
    public function getRangeItemCountsDataProvider()
    {
        return [
            // These are $inputRange, [$expectedItemCounts] values
            [1, [11 => 2, 46 => 1, 16 => '1']],
            [10, [2 => 3, 5 => 1]],
            [20, [1 => 3, 3 => 1]],
            [50, [1 => 4]]
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDbIsolation disabled
     * @dataProvider getRangeItemCountsDataProvider
     */
    public function testGetRangeItemCounts($inputRange, $expectedItemCounts)
    {
        $actualItemCounts = $this->_model->getRangeItemCounts($inputRange);
        $this->assertEquals($expectedItemCounts, $actualItemCounts);
    }
}
