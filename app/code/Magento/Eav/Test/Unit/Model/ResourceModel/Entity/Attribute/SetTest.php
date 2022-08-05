<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity\Attribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetTest extends TestCase
{
    /**
     * @var MockObject|Set
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $eavConfigMock;

    /**
     * @var MockObject
     */
    protected $objectMock;

    /**
     * @var MockObject
     */
    protected $typeMock;

    /**
     * @var MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     * @var MockObject
     */
    protected $relationProcessor;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->transactionManagerMock = $this->createMock(
            TransactionManagerInterface::class
        );
        $this->relationProcessor = $this->createMock(
            ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($this->transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessor);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isCacheEnabled', 'getEntityType', 'getCache'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->createMock(Json::class);

        $attributeGroupFactoryMock = $this->createMock(
            GroupFactory::class
        );

        $this->model = $objectManager->getObject(
            Set::class,
            [
                'context' => $contextMock,
                'attrGroupFactory' => $attributeGroupFactoryMock,
                'eavConfig' => $this->eavConfigMock
            ]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $this->serializerMock);

        $this->typeMock = $this->createMock(Type::class);
        $this->objectMock = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(['getEntityTypeId', 'getAttributeSetId'])
            ->onlyMethods(['beforeDelete', 'getId', 'isDeleted', 'afterDelete', 'afterDeleteCommit', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testBeforeDeleteStateException()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The default attribute set can\'t be deleted.');
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->getMockForAbstractClass(AdapterInterface::class));

        $this->transactionManagerMock->expects($this->once())
            ->method('start')
            ->with($this->getMockForAbstractClass(AdapterInterface::class))
            ->willReturn($this->getMockForAbstractClass(AdapterInterface::class));

        $this->objectMock->expects($this->once())->method('getEntityTypeId')->willReturn(665);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(665)->willReturn($this->typeMock);
        $this->typeMock->expects($this->once())->method('getDefaultAttributeSetId')->willReturn(4);
        $this->objectMock->expects($this->once())->method('getAttributeSetId')->willReturn(4);

        $this->model->delete($this->objectMock);
    }

    /**
     * @return void
     */
    public function testBeforeDelete()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('test exception');
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->getMockForAbstractClass(AdapterInterface::class));

        $this->transactionManagerMock->expects($this->once())
            ->method('start')
            ->with($this->getMockForAbstractClass(AdapterInterface::class))
            ->willReturn($this->getMockForAbstractClass(AdapterInterface::class));

        $this->objectMock->expects($this->once())->method('getEntityTypeId')->willReturn(665);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(665)->willReturn($this->typeMock);
        $this->typeMock->expects($this->once())->method('getDefaultAttributeSetId')->willReturn(4);
        $this->objectMock->expects($this->once())->method('getAttributeSetId')->willReturn(5);
        $this->relationProcessor->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('test exception'));

        $this->model->delete($this->objectMock);
    }

    /**
     * @return void
     */
    public function testGetSetInfoCacheMiss()
    {
        $serializedData = 'serialized data';
        $setElement = [
            10000 => [
                'group_id' => 10,
                'group_sort' => 100,
                'sort' => 1000
            ]
        ];
        $setData = [
            1 => $setElement,
            2 => [],
            3 => []
        ];
        $cached = [
            1 => $setElement
        ];
        $cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'getFrontend', 'remove', 'clean'])
            ->getMockForAbstractClass();
        $cacheKey = Set::ATTRIBUTES_CACHE_ID . 1;
        $cacheMock
            ->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($cached)
            ->willReturn($serializedData);
        $cacheMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                $cacheKey,
                [\Magento\Eav\Model\Cache\Type::CACHE_TAG, Attribute::CACHE_TAG]
            );

        $this->eavConfigMock->expects($this->any())->method('isCacheEnabled')->willReturn(true);
        $this->eavConfigMock->expects($this->any())->method('getCache')->willReturn($cacheMock);

        $fetchResult = [
            [
                'attribute_id' => 1,
                'attribute_group_id' => 10,
                'group_sort_order' => 100,
                'sort_order' => 1000,
                'attribute_set_id' => 10000
            ]
        ];

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'joinLeft', 'where'])
            ->getMock();
        $selectMock->expects($this->once())->method('from')->willReturnSelf();
        $selectMock->expects($this->once())->method('joinLeft')->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())->method('where')->willReturnSelf();

        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'fetchAll'])
            ->getMock();
        $connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $connectionMock->expects($this->atLeastOnce())->method('fetchAll')->willReturn($fetchResult);

        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->resourceMock->expects($this->any())->method('getTableName')->willReturn('_TABLE_');
        $this->assertEquals(
            $setData,
            $this->model->getSetInfo([1, 2, 3], 1)
        );
    }

    /**
     * @return void
     */
    public function testGetSetInfoCacheHit()
    {
        $setElement = [
            10000 => [
                'group_id' => 10,
                'group_sort' => 100,
                'sort' => 1000
            ]
        ];
        $setData = [
            1 => $setElement,
            2 => [],
            3 => []
        ];
        $cached = [
            1 => $setElement
        ];
        $serializedData = 'serialized data';
        $this->resourceMock->expects($this->never())->method('getConnection');
        $this->eavConfigMock->expects($this->any())->method('isCacheEnabled')->willReturn(true);
        $cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'getFrontend', 'remove', 'clean'])
            ->getMockForAbstractClass();
        $cacheMock->expects($this->once())
            ->method('load')
            ->with(Set::ATTRIBUTES_CACHE_ID . 1)
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($cached);

        $this->eavConfigMock->expects($this->any())->method('getCache')->willReturn($cacheMock);

        $this->assertEquals(
            $setData,
            $this->model->getSetInfo([1, 2, 3], 1)
        );
    }
}
