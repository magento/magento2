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
     * @var \Magento\Indexer\Model\IndexerInterface
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

        /** @var \Magento\Indexer\Model\IndexerRegistry $indexerRegistry */
        $indexerRegistry = $this->objectManager->create('\Magento\Indexer\Model\IndexerRegistry');
        $this->indexer =  $indexerRegistry->get('catalogsearch_fulltext');

        $this->resource = $this->objectManager->get('Magento\Framework\App\Resource');
        $this->adapter = $this->resource->getConnection('core_read');
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @covers \Magento\Indexer\Model\Indexer::reindexAll
     * @covers \Magento\Bundle\Model\Product\Type::getSearchableData
     */
    public function testPrepareProductIndexForBundleProduct()
    {
        $this->indexer->reindexAll();

        $select = $this->adapter->select()->from($this->resource->getTableName('catalogsearch_fulltext_scope1'))
            ->where('`data_index` LIKE ?', '%' . 'Bundle Product Items' . '%');

        $result = $this->adapter->fetchAll($select);
        $this->assertCount(1, $result);
    }
}
