<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Collection;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * AbstractCollection test
 *
 * Test for AbstractCollection class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractCollectionTest extends TestCase
{
    const ATTRIBUTE_CODE = 'any_attribute';
    const ATTRIBUTE_ID_STRING = '15';
    const ATTRIBUTE_ID_INT = 15;

    /**
     * @var AbstractCollectionStub|MockObject
     */
    protected $model;

    /**
     * @var EntityFactory|MockObject
     */
    protected $coreEntityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $coreResourceMock;

    /**
     * @var \Magento\Eav\Model\EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var Helper|MockObject
     */
    protected $resourceHelperMock;

    /**
     * @var UniversalFactory|MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var Mysql|MockObject
     */
    protected $statementMock;

    protected function setUp(): void
    {
        $this->coreEntityFactoryMock = $this->createMock(EntityFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            FetchStrategyInterface::class
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->coreResourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceHelperMock = $this->createMock(Helper::class);
        $this->validatorFactoryMock = $this->createMock(UniversalFactory::class);
        $this->entityFactoryMock = $this->createMock(\Magento\Eav\Model\EntityFactory::class);
        /** @var AdapterInterface|MockObject */
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->statementMock = $this->createPartialMock(Mysql::class, ['fetch']);
        /** @var Select|MockObject $selectMock */
        $selectMock = $this->createMock(Select::class);
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
        $entityMock = $this->createMock(AbstractEntity::class);
        $entityMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $entityMock->expects($this->any())->method('getDefaultAttributes')->willReturn([]);
        $entityMock->expects($this->any())->method('getLinkField')->willReturn('entity_id');

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('isStatic')->willReturn(false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn(self::ATTRIBUTE_CODE);
        $attributeMock->expects($this->any())->method('getBackendTable')->willReturn('eav_entity_int');
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn('int');
        $attributeMock->expects($this->any())->method('getId')->willReturn(self::ATTRIBUTE_ID_STRING);

        $entityMock
            ->expects($this->any())
            ->method('getAttribute')
            ->with(self::ATTRIBUTE_CODE)
            ->willReturn($attributeMock);

        $this->configMock
            ->expects($this->any())
            ->method('getAttribute')
            ->with(null, self::ATTRIBUTE_CODE)
            ->willReturn($attributeMock);

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
     * @dataProvider getItemsDataProvider
     */
    public function testAttributeIdIsInt($values)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');
        $this->resourceHelperMock->expects($this->any())->method('getLoadAttributesSelectGroups')->willReturn([]);
        $this->fetchStrategyMock->expects($this->any())->method('fetchAll')->willReturn($values);
        $selectMock = $this->coreResourceMock->getConnection()->select();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('join')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('where')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('columns')->willReturn($selectMock);

        $this->model
            ->addAttributeToSelect(self::ATTRIBUTE_CODE)
            ->_loadEntities()
            ->_loadAttributes();

        $_selectAttributesActualValue = $this->readAttribute($this->model, '_selectAttributes');

        $this->assertAttributeEquals(
            [self::ATTRIBUTE_CODE => self::ATTRIBUTE_ID_STRING],
            '_selectAttributes',
            $this->model
        );
        $this->assertSame($_selectAttributesActualValue[self::ATTRIBUTE_CODE], self::ATTRIBUTE_ID_INT);
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
     * @return DataObject
     */
    public function getMagentoObject()
    {
        return new DataObject();
    }
}
