<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Model\Indexer;

/**
 * @magentoDataFixture Magento/CatalogSearch/_files/indexer_fulltext.php
 */
class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     */
    protected $engine;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext
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

    protected function setUp()
    {
        /** @var \Magento\Indexer\Model\IndexerInterface indexer */
        $this->indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Indexer\Model\Indexer'
        );
        $this->indexer->load('catalogsearch_fulltext');

        $this->engine = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\CatalogSearch\Model\Resource\Fulltext\Engine'
        );

        $this->resourceFulltext = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\CatalogSearch\Model\Resource\Fulltext'
        );

        $this->fulltext = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\CatalogSearch\Model\Fulltext'
        );

        $this->queryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Search\Model\QueryFactory'
        );

        $this->productApple = $this->getProductBySku('fulltext-1');
        $this->productBanana = $this->getProductBySku('fulltext-2');
        $this->productOrange = $this->getProductBySku('fulltext-3');
        $this->productPapaya = $this->getProductBySku('fulltext-4');
        $this->productCherry = $this->getProductBySku('fulltext-5');
    }

    public function testReindexAll()
    {
        $this->engine->cleanIndex();

        $this->indexer->reindexAll();

        $products = $this->search('Apple');
        $this->assertCount(1, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());

        $products = $this->search('Simple Product');
        $this->assertCount(5, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());
        $this->assertEquals($this->productBanana->getId(), $products[1]->getId());
        $this->assertEquals($this->productOrange->getId(), $products[2]->getId());
        $this->assertEquals($this->productPapaya->getId(), $products[3]->getId());
        $this->assertEquals($this->productCherry->getId(), $products[4]->getId());
    }

    /**
     * @depends testReindexAll
     */
    public function testReindexRowAfterEdit()
    {
        $this->productApple->setData('name', 'Simple Product Cucumber');
        $this->productApple->save();

        $products = $this->search('Apple');
        $this->assertCount(0, $products);

        $products = $this->search('Cucumber');
        $this->assertCount(1, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());

        $products = $this->search('Simple Product');
        $this->assertCount(5, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());
        $this->assertEquals($this->productBanana->getId(), $products[1]->getId());
        $this->assertEquals($this->productOrange->getId(), $products[2]->getId());
        $this->assertEquals($this->productPapaya->getId(), $products[3]->getId());
        $this->assertEquals($this->productCherry->getId(), $products[4]->getId());
    }

    /**
     * @depends testReindexRowAfterEdit
     */
    public function testReindexRowAfterMassAction()
    {
        $productIds = [
            $this->productApple->getId(),
            $this->productBanana->getId(),
        ];
        $attrData = [
            'name' => 'Simple Product Common',
        ];

        /** @var \Magento\Catalog\Model\Product\Action $action */
        $action = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product\Action'
        );
        $action->updateAttributes($productIds, $attrData, 1);

        $products = $this->search('Apple');
        $this->assertCount(0, $products);

        $products = $this->search('Banana');
        $this->assertCount(0, $products);

        $products = $this->search('Unknown');
        $this->assertCount(0, $products);

        $products = $this->search('Common');
        $this->assertCount(2, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());
        $this->assertEquals($this->productBanana->getId(), $products[1]->getId());

        $products = $this->search('Simple Product');
        $this->assertCount(5, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());
        $this->assertEquals($this->productBanana->getId(), $products[1]->getId());
        $this->assertEquals($this->productOrange->getId(), $products[2]->getId());
        $this->assertEquals($this->productPapaya->getId(), $products[3]->getId());
        $this->assertEquals($this->productCherry->getId(), $products[4]->getId());
    }

    /**
     * @depends testReindexRowAfterMassAction
     * @magentoAppArea adminhtml
     */
    public function testReindexRowAfterDelete()
    {
        $this->productBanana->delete();

        $products = $this->search('Simple Product');
        $this->assertCount(4, $products);
        $this->assertEquals($this->productApple->getId(), $products[0]->getId());
        $this->assertEquals($this->productOrange->getId(), $products[1]->getId());
        $this->assertEquals($this->productPapaya->getId(), $products[2]->getId());
        $this->assertEquals($this->productCherry->getId(), $products[3]->getId());
    }

    /**
     * Search the text and return result collection
     *
     * @param string $text
     * @return \Magento\Catalog\Model\Product[]
     */
    protected function search($text)
    {
        $this->resourceFulltext->resetSearchResults();
        $query = $this->queryFactory->get();
        $query->unsetData()->setQueryText($text)->prepare();
        $products = [];
        $collection = $this->engine->getResultCollection();
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
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductBySku($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product'
        );
        return $product->loadByAttribute('sku', $sku);
    }
}
