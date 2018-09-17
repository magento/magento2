<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\DeleteAbandonedStoreFlatTables;
use Magento\Catalog\Helper\Product\Flat\Indexer;

/**
 * @covers \Magento\Catalog\Cron\DeleteAbandonedStoreFlatTables
 */
class DeleteAbandonedStoreFlatTablesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testable Object
     *
     * @var DeleteAbandonedStoreFlatTables
     */
    private $deleteAbandonedStoreFlatTables;

    /**
     * @var Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->indexerMock = $this->getMockBuilder(Indexer::class)->disableOriginalConstructor()->getMock();
        $this->deleteAbandonedStoreFlatTables = new DeleteAbandonedStoreFlatTables($this->indexerMock);
    }

    /**
     * Test execute method
     *
     * @return void
     */
    public function testExecute()
    {
        $this->indexerMock->expects($this->once())->method('deleteAbandonedStoreFlatTables');
        $this->deleteAbandonedStoreFlatTables->execute();
    }
}
