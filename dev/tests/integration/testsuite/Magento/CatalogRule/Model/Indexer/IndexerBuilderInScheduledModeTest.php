<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

class IndexerBuilderInScheduledModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RuleProductProcessor
     */
    private $ruleProductProcessor;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->ruleProductProcessor = Bootstrap::getObjectManager()->get(RuleProductProcessor::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture('Magento/Catalog/_files/product_with_options.php'),
        DataFixture('Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php'),
    ]
    public function testReindexOfDependentIndexer(): void
    {
        $indexer = $this->ruleProductProcessor->getIndexer();
        $indexer->reindexAll();
        $indexer->setScheduled(true);

        $product = $this->productRepository->get('simple');
        $productId = (int)$product->getId();

        $product = $this->getProductFromCollection($productId);
        $this->assertEquals(9, $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());

        $product->setPrice(100);
        $this->productRepository->save($product);

        $product = $this->getProductFromCollection($productId);
        $this->assertEquals(9, $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());

        $indexer->reindexList([$productId]);

        $product = $this->getProductFromCollection($productId);
        $this->assertEquals(90, $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());

        $indexer->setScheduled(false);
    }

    /**
     * Get the product from the product collection
     *
     * @param int $productId
     * @return DataObject
     */
    private function getProductFromCollection(int $productId) : DataObject
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($productId);
        $productCollection->addPriceData();
        $productCollection->load();
        return $productCollection->getFirstItem();
    }
}
