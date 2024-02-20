<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Limit total number of products in grid collection test.
 */
class LimitTotalNumberOfProductsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
    }

    /**
     * Test limit total number of products is enabled and limit is reached.
     *
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     */
    public function testLimitNumberOfProductsEnabled()
    {
        $productCollection = $this->objectManager->create(
            CollectionFactory::class,
            [
                ObjectManager::class,
                'instanceName' => ProductCollection::class
            ]
        );
        $dataProvider = $this->objectManager->create(
            ProductDataProvider::class,
            [
                'name' => 'product_listing_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
                'collectionFactory' => $productCollection
            ]
        );

        $this->scopeConfig->setValue(
            'admin/grid/limit_total_number_of_products',
            1
        );
        $this->scopeConfig->setValue(
            'admin/grid/records_limit',
            2
        );

        $data = $dataProvider->getData();
        $this->assertEquals(2, $data['totalRecords']);
    }

    /**
     * Test limit total number of products is enabled and limit is not reached.
     *
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     */
    public function testLimitNumberOfProductsEnabledAndLimitIsNotReached()
    {
        $productCollection = $this->objectManager->create(
            CollectionFactory::class,
            [
                ObjectManager::class,
                'instanceName' => ProductCollection::class
            ]
        );
        $dataProvider = $this->objectManager->create(
            ProductDataProvider::class,
            [
                'name' => 'product_listing_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
                'collectionFactory' => $productCollection
            ]
        );

        $this->scopeConfig->setValue(
            'admin/grid/limit_total_number_of_products',
            1
        );
        $this->scopeConfig->setValue(
            'admin/grid/records_limit',
            3
        );

        $data = $dataProvider->getData();
        $this->assertEquals(3, $data['totalRecords']);
    }

    /**
     * Test limit total number of products is disabled.
     *
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     */
    public function testLimitNumberOfProductsDisabled()
    {
        $productCollection = $this->objectManager->create(
            CollectionFactory::class,
            [
                ObjectManager::class,
                'instanceName' => ProductCollection::class
            ]
        );
        $dataProvider = $this->objectManager->create(
            ProductDataProvider::class,
            [
                'name' => 'product_listing_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
                'collectionFactory' => $productCollection
            ]
        );

        $this->scopeConfig->setValue(
            'admin/grid/limit_total_number_of_products',
            0
        );
        $this->scopeConfig->setValue(
            'admin/grid/records_limit',
            20000
        );

        $data = $dataProvider->getData();
        $this->assertEquals(3, $data['totalRecords']);
    }

    protected function tearDown(): void
    {
        $this->scopeConfig->setValue(
            'admin/grid/limit_total_number_of_products',
            0
        );
        $this->scopeConfig->setValue(
            'admin/grid/records_limit',
            20000
        );
    }
}
