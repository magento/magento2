<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Test class for \Magento\Bundle\Model\Product\Type (bundle product type)
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Full reindex
     *
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * Connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $adapter;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full indexer */
        $this->indexer =  $this->objectManager->create('Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full');

        $this->resource = $this->objectManager->get('Magento\Framework\App\Resource');
        $this->adapter = $this->resource->getConnection('core_read');
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @covers \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::reindexAll
     * @covers \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::prepareProductIndex
     * @covers \Magento\Bundle\Model\Product\Type::getSearchableData
     */
    public function testPrepareProductIndexForBundlePeoduct()
    {
        $this->indexer->reindexAll();

        $select = $this->adapter->select()->from($this->resource->getTableName('catalogsearch_fulltext'))
            ->where('`data_index` LIKE ?', '%' . 'Bundle Product Items' . '%');

        $result = $this->adapter->fetchAll($select);
        $this->assertCount(1, $result);
    }
}
