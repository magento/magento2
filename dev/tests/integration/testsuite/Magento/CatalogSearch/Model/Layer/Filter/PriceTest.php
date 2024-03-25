<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Layer\Category as CategoryLayer;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogSearch\Model\Layer\Filter\Price as PriceFilter;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\CatalogSearch\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PriceFilter
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->initializePriceFilter();
    }

    public function testApplyNothing()
    {
        $this->assertEmpty($this->model->getData('price_range'));
        /** @var $request \Magento\TestFramework\Request */
        $request = $this->objectManager->get(\Magento\TestFramework\Request::class);
        $this->model->apply($request);

        $this->assertEmpty($this->model->getData('price_range'));
    }

    public function testApplyInvalid()
    {
        $this->assertEmpty($this->model->getData('price_range'));
        /** @var $request \Magento\TestFramework\Request */
        $request = $this->objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('price', 'non-numeric');
        $this->model->apply($request);

        $this->assertEmpty($this->model->getData('price_range'));
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     */
    public function testApplyManual()
    {
        /** @var $request \Magento\TestFramework\Request */
        $request = $this->objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('price', '10-20');
        $this->model->apply($request);
    }

    /**
     * Make sure that currency rate is used to calculate label for applied price filter
     */
    public function testApplyWithCustomCurrencyRate()
    {
        /** @var $request \Magento\TestFramework\Request */
        $request = $this->objectManager->get(\Magento\TestFramework\Request::class);

        $request->setParam('price', '10-20');
        $this->model->setCurrencyRate(10);

        $this->model->apply($request);

        $filters = $this->model->getLayer()->getState()->getFilters();
        $this->assertArrayHasKey(0, $filters);
        $this->assertEquals(
            '<span class="price">$100.00</span> - <span class="price">$199.99</span>',
            (string)$filters[0]->getLabel()
        );
    }

    public function testGetSetCustomerGroupId()
    {
        $this->assertEquals(
            \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
            $this->model->getCustomerGroupId()
        );

        $customerGroupId = 123;
        $this->model->setCustomerGroupId($customerGroupId);

        $this->assertEquals($customerGroupId, $this->model->getCustomerGroupId());
    }

    public function testGetSetCurrencyRate()
    {
        $this->assertEquals(1, $this->model->getCurrencyRate());

        $currencyRate = 42;
        $this->model->setCurrencyRate($currencyRate);

        $this->assertEquals($currencyRate, $this->model->getCurrencyRate());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories_no_products.php
     */
    #[
        DbIsolation(false),
        Config('catalog/layered_navigation/price_range_calculation', 'manual', 'store'),
        Config('catalog/layered_navigation/price_range_step', '10', 'store'),
        Config('catalog/layered_navigation/price_range_max_intervals', '2', 'store'),
        DataFixture(ProductFixture::class, ['category_ids' => [4], 'price' => 11]),
        DataFixture(ProductFixture::class, ['category_ids' => [4], 'price' => 13]),
        DataFixture(ProductFixture::class, ['category_ids' => [4], 'price' => 22]),
        DataFixture(ProductFixture::class, ['category_ids' => [4], 'price' => 22]),
        DataFixture(ProductFixture::class, ['category_ids' => [4], 'price' => 110]),
    ]
    public function testGetItemsWithManualAlgorithm(): void
    {
        $attributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $priceAttribute = $attributeRepository->get('price');
        $this->model->setAttributeModel($priceAttribute);

        /** @var \Magento\Catalog\Model\Layer\Filter\Item[] $ranges */
        $ranges = $this->model->getItems();
        self::assertCount(2, $ranges);

        $request = $this->objectManager->get(\Magento\TestFramework\Request::class);
        foreach ($ranges as $range) {
            $request->setParam('price', $range->getValueString());
            $this->initializePriceFilter();
            $this->model->apply($request);
            $products = $this->model->getLayer()->getProductCollection()->getItems();
            self::assertCount($range->getCount(), $products);
        }
    }

    /**
     * @return void
     */
    private function initializePriceFilter(): void
    {
        $categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $category = $categoryRepository->get(4);
        $layer = $this->objectManager->create(CategoryLayer::class);
        $layer->setCurrentCategory($category);
        $this->model = $this->objectManager->create(
            PriceFilter::class,
            ['layer' => $layer]
        );
    }
}
