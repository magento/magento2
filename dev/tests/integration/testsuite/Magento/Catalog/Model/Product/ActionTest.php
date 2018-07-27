<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\CatalogSearch\Model\Indexer\Fulltext;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $action;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public static function setUpBeforeClass()
    {
        /** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
        $indexerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Indexer\IndexerRegistry::class);
        $indexerRegistry->get(Fulltext::INDEXER_ID)->setScheduled(true);
    }

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\App\Cache\StateInterface $cacheState */
        $cacheState = $this->objectManager->get(\Magento\Framework\App\Cache\StateInterface::class);
        $cacheState->setEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER, true);

        $this->action = $this->objectManager->create(\Magento\Catalog\Model\Product\Action::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testUpdateWebsites()
    {
        /** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository */
        $websiteRepository = $this->objectManager->create(\Magento\Store\Api\WebsiteRepositoryInterface::class);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        /** @var \Magento\Framework\App\CacheInterface $cacheManager */
        $pageCache = $this->objectManager->create(\Magento\PageCache\Model\Cache\Type::class);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productRepository->get('simple');
        foreach ($product->getCategoryIds() as $categoryId) {
            $pageCache->save(
                'test_data',
                'test_data_category_id_' . $categoryId,
                [\Magento\Catalog\Model\Category::CACHE_TAG . '_' . $categoryId]
            );
            $this->assertEquals('test_data', $pageCache->load('test_data_category_id_' . $categoryId));
        }

        $websites = $websiteRepository->getList();
        $websiteIds = [];
        foreach ($websites as $websiteCode => $website) {
            if (in_array($websiteCode, ['secondwebsite', 'thirdwebsite'])) {
                $websiteIds[] = $website->getId();
            }
        }

        $this->action->updateWebsites([$product->getId()], $websiteIds, 'add');

        foreach ($product->getCategoryIds() as $categoryId) {
            $this->assertEmpty(
                $pageCache->load('test_data_category_id_' . $categoryId)
            );
        }
    }

    public static function tearDownAfterClass()
    {
        /** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
        $indexerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Indexer\IndexerRegistry::class);
        $indexerRegistry->get(Fulltext::INDEXER_ID)->setScheduled(false);
    }
}
