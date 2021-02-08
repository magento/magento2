<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Collection;

/**
 * AbstractCollection test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbstractCollectionStub|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreEntityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreResourceMock;

    /**
     * @var \Magento\Eav\Model\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceHelperMock;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var \Magento\Framework\DB\Statement\Pdo\Mysql|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $statementMock;

    protected function setUp(): void
    {
        $this->coreEntityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->configMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->coreResourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resourceHelperMock = $this->createMock(\Magento\Eav\Model\ResourceModel\Helper::class);
        $this->validatorFactoryMock = $this->createMock(\Magento\Framework\Validator\UniversalFactory::class);
        $this->entityFactoryMock = $this->createMock(\Magento\Eav\Model\EntityFactory::class);
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->statementMock = $this->createPartialMock(\Magento\Framework\DB\Statement\Pdo\Mysql::class, ['fetch']);
        /** @var $selectMock \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject */
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->coreEntityFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturnCallback(
            [$this, 'getMagentoObject']
        );
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $connectionMock->expects($this->any())->method('query')->willReturn($this->statementMock);

        $this->coreResourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->willReturn(
            $connectionMock
        );
        $entityMock = $this->createMock(\Magento\Eav\Model\Entity\AbstractEntity::class);
        $entityMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $entityMock->expects($this->any())->method('getDefaultAttributes')->willReturn([]);

        $this->validatorFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'test_entity_model' // see \Magento\Eav\Test\Unit\Model\Entity\Collection\AbstractCollectionStub
        )->willReturn(
            $entityMock
        );

        $this->model = new AbstractCollectionStub(
            $this->coreEntityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->configMock,
            $this->coreResourceMock,
            $this->entityFactoryMock,
            $this->resourceHelperMock,
            $this->validatorFactoryMock,
            null
        );
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    /**
     * Test method \Magento\Eav\Model\Entity\Collection\AbstractCollection::load
     */
    public function testLoad()
    {
        $this->fetchStrategyMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'data_changes' => true], ['id' => 2]]);

        foreach ($this->model->getItems() as $item) {
            $this->assertFalse($item->getDataChanges());
        }
    }

    /**
     * @dataProvider getItemsDataProvider
     */
    public function testClear($values, $count)
    {
        $this->fetchStrategyMock->expects($this->once())->method('fetchAll')->willReturn($values);

        $testId = array_pop($values)['id'];
        $this->assertCount($count, $this->model->getItems());
        $this->assertNotNull($this->model->getItemById($testId));
        $this->model->clear();
        $this->assertNull($this->model->getItemById($testId));
    }

    /**
     * @dataProvider getItemsDataProvider
     */
    public function testRemoveAllItems($values, $count)
    {
        $this->fetchStrategyMock->expects($this->once())->method('fetchAll')->willReturn($values);

        $testId = array_pop($values)['id'];
        $this->assertCount($count, $this->model->getItems());
        $this->assertNotNull($this->model->getItemById($testId));
        $this->model->removeAllItems();
        $this->assertNull($this->model->getItemById($testId));
    }

    /**
     * @dataProvider getItemsDataProvider
     */
    public function testRemoveItemByKey($values, $count)
    {
        $this->fetchStrategyMock->expects($this->once())->method('fetchAll')->willReturn($values);

        $testId = array_pop($values)['id'];
        $this->assertCount($count, $this->model->getItems());
        $this->assertNotNull($this->model->getItemById($testId));
        $this->model->removeItemByKey($testId);
        $this->assertCount($count - 1, $this->model->getItems());
        $this->assertNull($this->model->getItemById($testId));
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        return [
            ['values' => [['id' => 1]], 'count' => 1],
            ['values' => [['id' => 1], ['id' => 2]], 'count' => 2],
            ['values' => [['id' => 2], ['id' => 3]], 'count' => 2]
        ];
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getMagentoObject()
    {
        return new \Magento\Framework\DataObject();
    }
}
