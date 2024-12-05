<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['from', 'where', 'join', 'deleteFromSelect'])
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(Adapter::class)
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->once())->method('delete')->willReturn($this->selectMock);

        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->addMethods(['delete'])
            ->onlyMethods(['getConnection'])
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->eavEntityTypeMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute'])
            ->getMock();
        $this->lockValidatorMock = $this->getMockBuilder(LockValidatorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMockForAbstractClass();
        $this->removeProductAttributeDataMock = $this->getMockBuilder(RemoveProductAttributeData::class)
            ->onlyMethods(['removeData'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
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

        $attributeModel = $this->getMockBuilder(Attribute::class)
            ->onlyMethods(['getEntityAttribute', 'getConnection', 'getTable'])
            ->setConstructorArgs([
                $this->contextMock,
                $this->storeManagerMock,
                $this->eavEntityTypeMock,
                $this->eavConfigMock,
                $this->lockValidatorMock,
                null,
                $this->removeProductAttributeDataMock
            ])->getMock();
        $attributeModel->expects($this->any())
            ->method('getEntityAttribute')
            ->with($entityAttributeId)
            ->willReturn($result);

        $eavAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavAttributeMock->expects($this->any())->method('getId')->willReturn($result['attribute_id']);

        $this->eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->with($entityTypeId, $result['attribute_id'])
            ->willReturn($eavAttributeMock);

        $abstractModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->addMethods(['getEntityAttributeId','getEntityTypeId'])
            ->getMockForAbstractClass();
        $abstractModelMock->expects($this->any())->method('getEntityAttributeId')->willReturn($entityAttributeId);
        $abstractModelMock->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);

        $this->lockValidatorMock->expects($this->any())
            ->method('validate')
            ->with($eavAttributeMock, $result['attribute_set_id'])
            ->willReturn(true);

        $backendModelMock = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->addMethods(['getBackend'])
            ->onlyMethods(['getTable'])
            ->getMock();

        $abstractAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntity'])
            ->getMockForAbstractClass();

        $eavAttributeMock->expects($this->any())->method('getBackend')->willReturn($backendModelMock);
        $eavAttributeMock->expects($this->any())->method('getEntity')->willReturn($abstractAttributeMock);

        $backendModelMock->expects($this->any())->method('getTable')->willReturn($backendTableName);

        $this->removeProductAttributeDataMock->expects($this->once())
            ->method('removeData')
            ->with($abstractModelMock, $result['attribute_set_id']);

        $attributeModel->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $attributeModel->expects($this->any())
            ->method('getTable')
            ->with('eav_entity_attribute')
            ->willReturn('eav_entity_attribute');

        $attributeModel->deleteEntity($abstractModelMock);
    }
}
