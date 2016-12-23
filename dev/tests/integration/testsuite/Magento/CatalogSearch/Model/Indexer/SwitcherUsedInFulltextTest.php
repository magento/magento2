<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/CatalogSearch/_files/indexer_fulltext.php
 */
class SwitcherUsedInFulltextTest extends \PHPUnit_Framework_TestCase
{
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
     * @var Product
     */
    protected $productApple;

    /**
     * @var Product
     */
    protected $productBanana;

    /**
     * @var Product
     */
    protected $productOrange;

    /**
     * @var Product
     */
    protected $productPapaya;

    /**
     * @var Product
     */
    protected $productCherry;

    /**
     * @var  \Magento\Framework\Search\Request\Dimension
     */
    protected $dimension;

    protected function setUp()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface indexer */
        $this->indexer = Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $this->indexer->load('catalogsearch_fulltext');

        $objectManager = Bootstrap::getObjectManager();
        $this->engine = $objectManager->get(
            \Magento\CatalogSearch\Model\ResourceModel\Engine::class
        );

        $this->resourceFulltext = Bootstrap::getObjectManager()->get(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext::class
        );

        $this->queryFactory = Bootstrap::getObjectManager()->get(
            \Magento\Search\Model\QueryFactory::class
        );

        $this->dimension = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Search\Request\Dimension::class,
            ['name' => 'scope', 'value' => '1']
        );

        $this->productApple = $this->getProductBySku('fulltext-1');
        $this->productBanana = $this->getProductBySku('fulltext-2');
        $this->productOrange = $this->getProductBySku('fulltext-3');
        $this->productPapaya = $this->getProductBySku('fulltext-4');
        $this->productCherry = $this->getProductBySku('fulltext-5');
    }

    public function testReindexAll()
    {
        $this->indexer->reindexAll();

        $products = $this->search('Apple');
        $this->assertCount(1, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());

        $products = $this->search('Simple Product');
        $this->assertCount(5, $products);
        /** @var \Magento\CatalogSearch\Model\Indexer\IndexSwitcherMock $indexSwitcher */
        $indexSwitcher = Bootstrap::getObjectManager()->get(
            \Magento\CatalogSearch\Model\Indexer\IndexSwitcherMock::class
        );
        $this->assertTrue($indexSwitcher->isSwitched());
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
