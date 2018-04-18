<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote\Item;

use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Mock class dependencies
     */
    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface'
        );
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $this->selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMock('Magento\Framework\Model\ResourceModel\Db\AbstractDb', [], [], '', false);
        $this->resourceMock->expects($this->any())->method('getConnection')->will(
            $this->returnValue($this->connectionMock)
        );

        $objectManager = new ObjectManager($this);
        $this->collection = $objectManager->getObject(
            'Magento\Quote\Model\ResourceModel\Quote\Item\Collection',
            [
                'entityFactory' => $this->entityFactoryMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->eventManagerMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection',
            $this->collection
        );
    }
}
