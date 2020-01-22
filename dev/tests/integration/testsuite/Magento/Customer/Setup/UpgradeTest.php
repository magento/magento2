<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Shell;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for recurring data setup task
 */
class UpgradeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Indexer\Model\Indexer */
    protected $indexer;

    /** @var Shell */
    protected $shell;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $indexerRegistry = $objectManager->create(IndexerRegistry::class);
        $this->indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $this->shell = $objectManager->create(Shell::class);
    }

    /**
     * Clean up shared dependencies
     */
    protected function tearDown()
    {
        $this->indexer->reindexAll();
    }

    public function testSetupUpgrade()
    {
        $this->shell->execute(
            PHP_BINARY . ' -f %s setup:upgrade',
            [BP . '/bin/magento']
        );

        $this->assertSame(
            StateInterface::STATUS_INVALID,
            $this->indexer->getStatus()
        );
    }
}
