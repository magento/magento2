<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;
    /**
     * @var Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;
    /**
     * @var Fulltext
     */
    private $target;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('\Magento\Framework\Model\ResourceModel\Db\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResources')
            ->willReturn($this->resource);
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $objectManager = new ObjectManager($this);
        $this->target = $objectManager->getObject(
            '\Magento\CatalogSearch\Model\ResourceModel\Fulltext',
            [
                'context' => $this->context,
            ]
        );
    }

    public function testResetSearchResult()
    {
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('search_query', ResourceConnection::DEFAULT_CONNECTION)
            ->willReturn('table_name_search_query');
        $this->connection->expects($this->once())
            ->method('update')
            ->with('table_name_search_query', ['is_processed' => 0], ['is_processed != 0'])
            ->willReturn(10);
        $result = $this->target->resetSearchResults();
        $this->assertEquals($this->target, $result);
    }
}
