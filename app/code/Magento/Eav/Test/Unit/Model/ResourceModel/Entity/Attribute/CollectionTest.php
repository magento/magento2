<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity\Attribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $model;

    /**
     * @var EntityFactory|MockObject
     */
    private $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    private $fetchStrategyMock;

    /**
     * @var EntityFactory|MockObject
     */
    private $eventManagerMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractDb|MockObject
     */
    private $resourceMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->getMockBuilder(EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fetchStrategyMock = $this->getMockBuilder(FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->onlyMethods(['__wakeup', 'getConnection', 'getMainTable', 'getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('eav_entity_attribute');

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger'        => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager'  => $this->eventManagerMock,
                'eavConfig'     => $this->eavConfigMock,
                'connection'    => $this->connectionMock,
                'resource'      => $this->resourceMock,
            ]
        );
    }

    /**
     * Test method \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::setInAllAttributeSetsFilter
     *
     * @return void
     */
    public function testSetInAllAttributeSetsFilter()
    {
        $setIds = [1, 2, 3];

        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->with('entity_attribute.attribute_set_id IN (?)', $setIds)
            ->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('join')->with(
            ['entity_attribute' => $this->model->getTable('eav_entity_attribute')],
            'entity_attribute.attribute_id = main_table.attribute_id',
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        )->willReturnSelf();

        $this->selectMock->expects($this->atLeastOnce())->method('group')->with('entity_attribute.attribute_id')
            ->willReturnSelf();

        $this->selectMock->expects($this->atLeastOnce())->method('having')
            ->with(new \Zend_Db_Expr('COUNT(*)') . ' = ' . count($setIds))->willReturnSelf();

        $this->model->setInAllAttributeSetsFilter($setIds);
    }
}
