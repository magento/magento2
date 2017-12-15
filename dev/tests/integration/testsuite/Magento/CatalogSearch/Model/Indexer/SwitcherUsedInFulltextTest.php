<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\IndexSwitcherMock;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/CatalogSearch/_files/indexer_fulltext.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitcherUsedInFulltextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IndexSwitcherInterface
     */
    private $indexSwitcher;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Engine
     */
    protected $engine;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext
     */
    protected $resourceFulltext;

    /**
     * @var \Magento\CatalogSearch\Model\Fulltext
     */
    protected $fulltext;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productApple;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productBanana;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productOrange;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productPapaya;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productCherry;

    /**
     * @var  \Magento\Framework\Search\Request\Dimension
     */
    protected $dimension;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->configure(
            [
                ltrim(Fulltext::class, '\\') => [
                    'arguments' => [
                        'indexSwitcher' => [
                            'instance' => ltrim(IndexSwitcherMock::class, '\\'),
                        ],
                    ],
                ],
            ]
        );

        /** @var \Magento\Framework\Indexer\IndexerInterface indexer */
        $this->indexer = $objectManager->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $this->indexer->load('catalogsearch_fulltext');

        $this->engine = $objectManager->get(
            \Magento\CatalogSearch\Model\ResourceModel\Engine::class
        );

        $this->resourceFulltext = $objectManager->get(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext::class
        );

        $this->queryFactory = $objectManager->get(
            \Magento\Search\Model\QueryFactory::class
        );

        $this->dimension = $objectManager->create(
            \Magento\Framework\Search\Request\Dimension::class,
            ['name' => 'scope', 'value' => '1']
        );

        $this->indexSwitcher = Bootstrap::getObjectManager()->get(
            IndexSwitcherMock::class
        );

        $this->productApple = $this->getProductBySku('fulltext-1');
        $this->productBanana = $this->getProductBySku('fulltext-2');
        $this->productOrange = $this->getProductBySku('fulltext-3');
        $this->productPapaya = $this->getProductBySku('fulltext-4');
        $this->productCherry = $this->getProductBySku('fulltext-5');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testReindexAll()
    {
        $this->indexer->reindexAll();

        /** @var IndexSwitcherMock $indexSwitcher */
        $indexSwitcher = Bootstrap::getObjectManager()->get(
            IndexSwitcherMock::class
        );
        $this->assertTrue($indexSwitcher->isSwitched());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testReindexList()
    {
        $this->indexer->reindexList([$this->productApple->getId(), $this->productBanana->getId()]);

        /** @var IndexSwitcherMock $indexSwitcher */
        $indexSwitcher = Bootstrap::getObjectManager()->get(
            IndexSwitcherMock::class
        );
        $this->assertFalse($indexSwitcher->isSwitched());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testReindexRow()
    {
        $this->indexer->reindexRow($this->productPapaya->getId());

        /** @var IndexSwitcherMock $indexSwitcher */
        $indexSwitcher = Bootstrap::getObjectManager()->get(
            IndexSwitcherMock::class
        );
        $this->assertFalse($indexSwitcher->isSwitched());
    }

    /**
     * Search the text and return result collection
     *
     * @param string $text
     * @return Product[]
     */
    protected function search($text)
    {
        $this->resourceFulltext->resetSearchResults();
        $query = $this->queryFactory->get();
        $query->unsetData();
        $query->setQueryText($text);
        $query->saveIncrementalPopularity();
        $products = [];
        $collection = Bootstrap::getObjectManager()->create(
            Collection::class,
            [
                'searchRequestName' => 'quick_search_container'
            ]
        );
        $collection->addSearchFilter($text);
        foreach ($collection as $product) {
            $products[] = $product;
        }
        return $products;
    }

    /**
     * Return product by SKU
     *
     * @param string $sku
     * @return Product
     */
    protected function getProductBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Product::class
        );
        return $product->loadByAttribute('sku', $sku);
    }
}
