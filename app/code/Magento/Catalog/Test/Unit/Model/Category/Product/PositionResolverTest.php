<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Product;

use Magento\Catalog\Model\Category\Product\PositionResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PositionResolverTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resources;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var PositionResolver
     */
    private $model;

    /**
     * @var array
     */
    private $positions = [
        '3' => 100,
        '2' => 101,
        '1' => 102
    ];

    /**
     * @var array
     */
    private $flippedPositions = [
        '100' => 3,
        '101' => 2,
        '102' => 1
    ];

    /**
     * @var int
     */
    private $categoryId = 1;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resources = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            PositionResolver::class,
            [
                'context' => $this->context,
                null,
                '_resources' => $this->resources
            ]
        );
    }

    public function testGetPositions()
    {
        $this->resources->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($this->select);
        $this->select->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $this->select->expects($this->exactly(2))
            ->method('order')
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('joinLeft')
            ->willReturnSelf();
        $this->connection->expects($this->once())
            ->method('fetchCol')
            ->willReturn($this->positions);

        $this->assertEquals($this->flippedPositions, $this->model->getPositions($this->categoryId));
    }
}
