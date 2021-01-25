<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Base alert's tab test logic
 */
abstract class AbstractAlertTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var RequestInterface */
    protected $request;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ProductResource */
    private $productResource;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $objectManager = Bootstrap::getObjectManager();
        /** @var Manager $moduleManager */
        $moduleManager = $objectManager->get(Manager::class);
        //This check is needed because module Magento_Catalog is independent of Magento_ProductAlert
        if (!$moduleManager->isEnabled('Magento_ProductAlert')) {
            self::markTestSkipped('Magento_ProductAlert module disabled.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
    }

    /**
     * Prepare request
     *
     * @param string|null $sku
     * @param string|null $storeCode
     * @return void
     */
    protected function prepareRequest(?string $sku = null, ?string $storeCode = null): void
    {
        $productId = (int)$this->productResource->getIdBySku($sku);
        $storeId = $storeCode ? (int)$this->storeManager->getStore($storeCode)->getId() : null;
        $this->request->setParams(['id' => $productId, 'store' => $storeId]);
    }

    /**
     * Assert grid url
     *
     * @param string $url
     * @param string|null $storeCode
     * @return void
     */
    protected function assertGridUrl(string $url, ?string $storeCode): void
    {
        $storeId = $storeCode ? (int)$this->storeManager->getStore($storeCode)->getId() : Store::DEFAULT_STORE_ID;
        $this->assertStringContainsString(sprintf('/store/%s', $storeId), $url);
    }
}
