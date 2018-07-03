<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Adapter\AdapterInterface as Adapter;
use Magento\ResourceConnections\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var Adapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavEntityTypeMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var LockValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lockValidatorMock;

    /**
     * @var EntityMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMetaDataInterfaceMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'where', 'join', 'deleteFromSelect'])
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(Adapter::class)->getMockForAbstractClass();
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->once())->method('query')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->once())->method('delete')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('join')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('deleteFromSelect')->willReturnSelf();

        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'getConnection'])
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->eavEntityTypeMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $this->lockValidatorMock = $this->getMockBuilder(LockValidatorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
         $this->entityMetaDataInterfaceMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Sets object non-public property.
     *
     * @param mixed $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return void
     */
    private function setObjectProperty($object, string $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @return void
     */
    public function testDeleteEntity()
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
        $backendFieldName = 'value_id';

        $attributeModel = $this->getMockBuilder(Attribute::class)
            ->setMethods(['getEntityAttribute', 'getMetadataPool', 'getConnection', 'getTable'])
            ->setConstructorArgs([
                $this->contextMock,
                $this->storeManagerMock,
                $this->eavEntityTypeMock,
                $this->eavConfigMock,
                $this->lockValidatorMock,
                null,
            ])->getMock();
        $attributeModel->expects($this->any())
            ->method('getEntityAttribute')
            ->with($entityAttributeId)
            ->willReturn($result);
        $metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata'])
            ->getMock();

        $this->setObjectProperty($attributeModel, 'metadataPool', $metadataPoolMock);

        $eavAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavAttributeMock->expects($this->any())->method('getId')->willReturn($result['attribute_id']);

        $this->eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->with($entityTypeId, $result['attribute_id'])
            ->willReturn($eavAttributeMock);

        $abstractModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityAttributeId','getEntityTypeId'])
            ->getMockForAbstractClass();
        $abstractModelMock->expects($this->any())->method('getEntityAttributeId')->willReturn($entityAttributeId);
        $abstractModelMock->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);

        $this->lockValidatorMock->expects($this->any())
            ->method('validate')
            ->with($eavAttributeMock, $result['attribute_set_id'])
            ->willReturn(true);

        $backendModelMock = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBackend', 'getTable', 'getEntityIdField'])
            ->getMock();

        $abstractAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntity'])
            ->getMockForAbstractClass();

        $eavAttributeMock->expects($this->any())->method('getBackend')->willReturn($backendModelMock);
        $eavAttributeMock->expects($this->any())->method('getEntity')->willReturn($abstractAttributeMock);

        $backendModelMock->expects($this->any())->method('getTable')->willReturn($backendTableName);
        $backendModelMock->expects($this->once())->method('getEntityIdField')->willReturn($backendFieldName);

        $metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetaDataInterfaceMock);

        $this->entityMetaDataInterfaceMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn('row_id');

        $attributeModel->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $attributeModel->expects($this->any())
            ->method('getTable')
            ->with('eav_entity_attribute')
            ->willReturn('eav_entity_attribute');

        $attributeModel->deleteEntity($abstractModelMock);
    }
}
