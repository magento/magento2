<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Catalog\Model\ResourceModel\Attribute\RemoveProductAttributeData;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface as Adapter;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Adapter|MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Type|MockObject
     */
    private $eavEntityTypeMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var LockValidatorInterface|MockObject
     */
    private $lockValidatorMock;

    /**
     * @var RemoveProductAttributeData|MockObject
     */
    private $removeProductAttributeDataMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                PoisonPillPutInterface::class,
                $this->createMock(PoisonPillPutInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->selectMock = $this->createPartialMock(
            Select::class,
            ['from', 'where', 'join', 'deleteFromSelect']
        );

        $this->connectionMock = $this->createMock(Adapter::class);
        $this->connectionMock->expects($this->once())->method('delete')->willReturn($this->selectMock);

        $this->resourceMock = $this->createPartialMockWithReflection(
            ResourceConnection::class,
            ['getConnection']
        );
        $this->resourceMock->method('getConnection')->willReturn($this->connectionMock);

        $this->contextMock = $this->createMock(Context::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->eavEntityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock = $this->createPartialMock(Config::class, ['getAttribute']);
        $this->lockValidatorMock = $this->createMock(LockValidatorInterface::class);
        $this->removeProductAttributeDataMock = $this->createPartialMock(
            RemoveProductAttributeData::class,
            ['removeData']
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDeleteEntity() : void
    {
        $entityAttributeId = 196;
        $entityTypeId = 4;
        $result = [
            'entity_attribute_id' => 196,
            'entity_type_id' => 4,
            'attribute_set_id'=> 4,
            'attribute_group_id' => 7,
            'attribute_id' => 177,
            'sort_order' => 3,
        ];

        $backendTableName = 'weee_tax';

        $attributeModel = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getEntityAttribute', 'getConnection', 'getTable'],
            [
                $this->contextMock,
                $this->storeManagerMock,
                $this->eavEntityTypeMock,
                $this->eavConfigMock,
                $this->lockValidatorMock,
                null,
                $this->removeProductAttributeDataMock
            ]
        );
        
        $attributeModel->expects($this->any())
            ->method('getEntityAttribute')
            ->with($entityAttributeId)
            ->willReturn($result);

        $eavAttributeMock = $this->createMock(AbstractAttribute::class);

        $eavAttributeMock->method('getId')->willReturn($result['attribute_id']);

        $this->eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->with($entityTypeId, $result['attribute_id'])
            ->willReturn($eavAttributeMock);

        $abstractModelMock = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['setEntityAttributeId', 'getEntityAttributeId', 'setEntityTypeId', 'getEntityTypeId', 'getId']
        );
        $entityAttrId = null;
        $entTypeId = null;
        $abstractModelMock->method('setEntityAttributeId')->willReturnCallback(
            function ($id) use (&$entityAttrId, $abstractModelMock) {
                $entityAttrId = $id;
                return $abstractModelMock;
            }
        );
        $abstractModelMock->method('getEntityAttributeId')->willReturnCallback(
            function () use (&$entityAttrId) {
                return $entityAttrId;
            }
        );
        $abstractModelMock->method('setEntityTypeId')->willReturnCallback(
            function ($id) use (&$entTypeId, $abstractModelMock) {
                $entTypeId = $id;
                return $abstractModelMock;
            }
        );
        $abstractModelMock->method('getEntityTypeId')->willReturnCallback(
            function () use (&$entTypeId) {
                return $entTypeId;
            }
        );
        $abstractModelMock->setEntityAttributeId($entityAttributeId);
        $abstractModelMock->setEntityTypeId($entityTypeId);

        $this->lockValidatorMock->expects($this->any())
            ->method('validate')
            ->with($eavAttributeMock, $result['attribute_set_id'])
            ->willReturn(true);

        $backendModelMock = $this->createPartialMockWithReflection(
            AbstractBackend::class,
            ['setTable', 'getTable']
        );
        $table = null;
        $backendModelMock->method('setTable')->willReturnCallback(
            function ($tbl) use (&$table, $backendModelMock) {
                $table = $tbl;
                return $backendModelMock;
            }
        );
        $backendModelMock->method('getTable')->willReturnCallback(
            function () use (&$table) {
                return $table;
            }
        );

        $abstractAttributeMock = $this->createMock(AbstractAttribute::class);

        $eavAttributeMock->method('getBackend')->willReturn($backendModelMock);
        $eavAttributeMock->method('getEntity')->willReturn($abstractAttributeMock);

        $backendModelMock->setTable($backendTableName);

        $this->removeProductAttributeDataMock->expects($this->once())
            ->method('removeData')
            ->with($abstractModelMock, $result['attribute_set_id']);

        $attributeModel->method('getConnection')->willReturn($this->connectionMock);
        $attributeModel->expects($this->any())
            ->method('getTable')
            ->with('eav_entity_attribute')
            ->willReturn('eav_entity_attribute');

        $attributeModel->deleteEntity($abstractModelMock);
    }
}
