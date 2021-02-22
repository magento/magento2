<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity\Attribute;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eavConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fetchStrategyMock = $this->getMockBuilder(FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->setMethods(['__wakeup', 'getConnection', 'getMainTable', 'getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('eav_entity_attribute');

        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class,
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
