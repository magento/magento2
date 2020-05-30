<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\Indexer\Product\Flat\Action\Full as FlatIndexerFull;

/**
 * Test relation customization
 */
class RelationTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var FlatIndexerFull
     */
    private $indexer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * Updated flat tables
     *
     * @var array
     */
    private $flatUpdated = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $tableBuilderMock = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder::class);
        $flatTableBuilderMock = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder::class);

        $productIndexerHelper = $objectManager->create(
            \Magento\Catalog\Helper\Product\Flat\Indexer::class,
            ['addChildData' => 1]
        );
        $this->indexer = $objectManager->create(
            FlatIndexerFull::class,
            [
                'productHelper' => $productIndexerHelper,
                'tableBuilder' => $tableBuilderMock,
                'flatTableBuilder' => $flatTableBuilderMock
            ]
        );
        $this->storeManager = $objectManager->create(StoreManagerInterface::class);
        $this->connection = $objectManager->get(ResourceConnection::class)->getConnection();

        foreach ($this->storeManager->getStores() as $store) {
            $flatTable = $productIndexerHelper->getFlatTableName($store->getId());
            if ($this->connection->isTableExists($flatTable) &&
                !$this->connection->tableColumnExists($flatTable, 'child_id') &&
                !$this->connection->tableColumnExists($flatTable, 'is_child')
            ) {
                $this->connection->addColumn(
                    $flatTable,
                    'child_id',
                    [
                        'type' => 'integer',
                        'length' => null,
                        'unsigned' => true,
                        'nullable' => true,
                        'default' => null,
                        'unique' => true,
                        'comment' => 'Child Id',
                    ]
                );
                $this->connection->addColumn(
                    $flatTable,
                    'is_child',
                    [
                        'type' => 'smallint',
                        'length' => 1,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Checks If Entity Is Child',
                    ]
                );

                $this->flatUpdated[] = $flatTable;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->flatUpdated as $flatTable) {
            $this->connection->dropColumn($flatTable, 'child_id');
            $this->connection->dropColumn($flatTable, 'is_child');
        }
    }

    /**
     * Test that SQL generated for relation customization is valid
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function testExecute() : void
    {
        $this->markTestSkipped('MC-19675');
        try {
            $this->indexer->execute();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getPrevious() instanceof \Zend_Db_Statement_Exception) {
                $this->fail($e->getMessage());
            }
            throw $e;
        }
    }
}
