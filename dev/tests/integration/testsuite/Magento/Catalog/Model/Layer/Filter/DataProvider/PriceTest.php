<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Layer\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\DataProvider\Price.
 */
class PriceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Price */
    private $model;

    /** @var Resolver */
    private $layerResolver;

    /** @var Category */
    private $layer;

    /** @var PriceFactory */
    private $dataProviderPriceFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layerResolver = $this->objectManager->get(Resolver::class);
        $this->layer = $this->layerResolver->get();
        $this->dataProviderPriceFactory = $this->objectManager->get(PriceFactory::class);
        $this->model = $this->dataProviderPriceFactory->create(['layer' => $this->layer]);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation auto
     * @return void
     */
    public function testGetPriceRangeAuto(): void
    {
        $this->layer->setCurrentCategory(4);
        $this->assertEquals(10, $this->model->getPriceRange());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step 1.5
     * @return void
     */
    public function testGetPriceRangeManual(): void
    {
        // what you set is what you get
        $this->layer->setCurrentCategory(4);
        $this->assertEquals(1.5, $this->model->getPriceRange());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testGetMaxPriceInt(): void
    {
        $this->layer->setCurrentCategory(4);
        $this->assertEquals(45.00, $this->model->getMaxPrice());
    }

    /**
     * @return array
     */
    public function getRangeItemCountsDataProvider(): array
    {
        return [
            // These are $inputRange, [$expectedItemCounts] values
            [1, [11 => 2, 46 => 1, 16 => '1']],
            [10, [2 => 3, 5 => 1]],
            [20, [1 => 3, 3 => 1]],
            [50, [1 => 4]],
        ];
    }

    /**
     * @dataProvider getRangeItemCountsDataProvider
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @param int $inputRange
     * @param array $expectedItemCounts
     * @return void
     */
    public function testGetRangeItemCounts(int $inputRange, array $expectedItemCounts): void
    {
        $this->layer->setCurrentCategory(4);
        $actualItemCounts = $this->model->getRangeItemCounts($inputRange);
        $this->assertEquals($expectedItemCounts, $actualItemCounts);
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_max_intervals 3
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testGetRangeItemCountsManualCalculation(): void
    {
        $expectedItemCounts = [11 => '2', 21 => '1', 31 => 2];
        $this->layer->setCurrentCategory(333);
        $actualItemCounts = $this->model->getRangeItemCounts(1);
        $this->assertEquals($expectedItemCounts, $actualItemCounts);
    }

    /**
     * @dataProvider getAdditionalRequestDataDataProvider
     * @param array $priceFilters
     * @param string $expectedRequest
     * @return void
     */
    public function testGetAdditionalRequestData(array $priceFilters, string $expectedRequest): void
    {
        $filter = explode('-', $priceFilters[0]);
        $this->model->setInterval($filter);
        $priorFilters = $this->model->getPriorFilters($priceFilters);
        if (!empty($priorFilters)) {
            $this->model->setPriorIntervals($priorFilters);
        }

        $actualRequest = $this->model->getAdditionalRequestData();
        $this->assertEquals($expectedRequest, $actualRequest);
    }

    /**
     * @return array
     */
    public function getAdditionalRequestDataDataProvider(): array
    {
        return [
            'with_prior_filters' => [
                'price_filters' => ['10-11', '20-21', '30-31'],
                'expected_request' => ',10-11,20-21,30-31',
            ],
            'without_prior_filters' => [
                'price_filters' => ['10-11'],
                'expected_request' => ',10-11',
            ],
            'not_valid_prior_filters' => [
                'price_filters' => ['10-11', '20-21', '31', '40-41'],
                'expected_request' => ',10-11',
            ],
        ];
    }
}
