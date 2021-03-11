<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as AttributeStatus;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class WebsiteAttributesSynchronizerTest
 * @package Magento\Catalog\Model\ResourceModel\Attribute
 */
class WebsiteAttributesSynchronizerTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_ID = 333;
    const PRODUCT_NOT_EDIT_MODE = false;
    const FIRST_STORE_CODE = 'customstoreview1';
    const SECOND_STORE_CODE = 'customstoreview2';
    const PRODUCT_FORCE_RELOAD = true;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/website_attribute_sync_flag.php
     * @magentoDbIsolation disabled
     */
    public function testScheduleSynchronization()
    {
        $instance = $this->getWebsiteAttributesSynchronizer();
        $this->assertFalse($instance->isSynchronizationRequired());
        $instance->scheduleSynchronization();
        $this->assertTrue($instance->isSynchronizationRequired());
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/website_attribute_sync.php
     * @magentoDbIsolation disabled
     */
    public function testSynchronizeSuccess()
    {
        $firstStore = $this->storeRepository->get(self::FIRST_STORE_CODE);
        $secondStore = $this->storeRepository->get(self::SECOND_STORE_CODE);

        $firstStoreProduct = $this->productRepository->getById(
            self::PRODUCT_ID,
            self::PRODUCT_NOT_EDIT_MODE,
            $firstStore->getId()
        );

        $secondStoreProduct = $this->productRepository->getById(
            self::PRODUCT_ID,
            self::PRODUCT_NOT_EDIT_MODE,
            $secondStore->getId()
        );

        $instance = $this->getWebsiteAttributesSynchronizer();

        $this->assertNotEquals($firstStoreProduct->getStatus(), $secondStoreProduct->getStatus());
        $this->assertEquals(AttributeStatus::STATUS_DISABLED, $firstStoreProduct->getStatus());
        $this->assertEquals(AttributeStatus::STATUS_ENABLED, $secondStoreProduct->getStatus());
        $this->assertTrue($instance->isSynchronizationRequired());

        $instance->synchronize();

        $firstStoreProductAfterSync = $this->productRepository->getById(
            self::PRODUCT_ID,
            self::PRODUCT_NOT_EDIT_MODE,
            $firstStore->getId(),
            self::PRODUCT_FORCE_RELOAD
        );

        $secondStoreProductAfterSync = $this->productRepository->getById(
            self::PRODUCT_ID,
            self::PRODUCT_NOT_EDIT_MODE,
            $secondStore->getId(),
            self::PRODUCT_FORCE_RELOAD
        );

        $this->assertEquals($firstStoreProductAfterSync->getStatus(), $secondStoreProductAfterSync->getStatus());
        $this->assertFalse($instance->isSynchronizationRequired());
    }

    /**
     * @return WebsiteAttributesSynchronizer
     */
    private function getWebsiteAttributesSynchronizer()
    {
        return $this->objectManager->get(WebsiteAttributesSynchronizer::class);
    }
}
