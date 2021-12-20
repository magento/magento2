<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_with_stores.php
     * @magentoDbIsolation disabled
     */
    public function testAfterReplace(): void
    {
        $storeId = 1;
        $categoryId = 4;
        $productId = $this->productRepository->get('simple')->getId();

        $urlRewriteFinder = $this->objectManager->get(UrlRewriteFinder::class);
        $rewrites = $urlRewriteFinder->findAllByData($productId, $storeId, UrlRewriteFinder::ENTITY_TYPE_PRODUCT);
        /**
         * @var $rewrite UrlRewrite
         */
        $rewrite = $rewrites[0];
        $rewrite->setMetadata(['category_id' => $categoryId]);

        $dbStorage = $this->objectManager->get(\Magento\UrlRewrite\Model\Storage\DbStorage::class);
        $dbStorage->replace([$rewrite]);
        $dbStorage->replace([$rewrite]);  // we assume this to fail with a unique key constraint violation
    }
}
