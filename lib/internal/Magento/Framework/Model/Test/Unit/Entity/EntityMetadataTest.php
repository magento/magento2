<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\Entity\EntityMetadata;

/**
 * Class EntityMetadataTest
 */
class EntityMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sequenceInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->appResourceMock = $this->getMockBuilder(AppResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceInterfaceMock = $this->getMockBuilder(SequenceInterface::class)->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetIdentifierField()
    {
        $identifierId = 'blabla_id';
        $metadata = new EntityMetadata($this->appResourceMock, "", $identifierId);
        $this->assertEquals($identifierId, $metadata->getIdentifierField());
    }

    public function testGetLinkField()
    {
        $entityTableName = 'entity_table';
        $connectionName = 'default';
        $this->appResourceMock->expects($this->exactly(2))
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($this->connectionMock);
        $this->appResourceMock->expects($this->exactly(2))
            ->method('getTableName')
            ->with($entityTableName)
            ->willReturn($entityTableName);
        $linkField = 'entity_id';
        $primaryKeyName = 'id';
        $indexList = [$primaryKeyName => ['COLUMNS_LIST' => [$linkField]]];
        $this->connectionMock->expects($this->once())
            ->method('getIndexList')
            ->with($entityTableName)
            ->willReturn($indexList);
        $this->connectionMock->expects($this->once())
            ->method('getPrimaryKeyName')
            ->with($entityTableName)
            ->willReturn($primaryKeyName);
        $metadata = new EntityMetadata($this->appResourceMock, $entityTableName, '', null, null, $connectionName);
        $this->assertEquals($linkField, $metadata->getLinkField());
    }

    public function testGenerateIdentifier()
    {
        $nextIdentifier = "42";
        $metadata = new EntityMetadata($this->appResourceMock, '', '', $this->sequenceInterfaceMock);
        $this->sequenceInterfaceMock->expects($this->once())->method('getNextValue')->willReturn($nextIdentifier);
        $this->assertEquals($nextIdentifier, $metadata->generateIdentifier());
    }

    public function testGenerateIdentifierWithoutSequence()
    {
        $metadata = new EntityMetadata($this->appResourceMock, '', '');
        $this->sequenceInterfaceMock->expects($this->never())->method('getNextValue');
        $this->assertNull($metadata->generateIdentifier());
    }

    public function testGetEntityContext()
    {
        $entityContext = ["store_id", "user_id"];
        $metadata = new EntityMetadata($this->appResourceMock, '', '', null, null, null, $entityContext);
        $this->assertEquals($entityContext, $metadata->getEntityContext());
    }

    public function testGetEavEntityType()
    {
        $eavEntityType = 'type';
        $metadata = new EntityMetadata($this->appResourceMock, '', '', null, $eavEntityType, null);
        $this->assertEquals($eavEntityType, $metadata->getEavEntityType());
    }

    public function testGetExtensionFields()
    {
        $fields = ['name'];
        $metadata = new EntityMetadata($this->appResourceMock, '', '', null, null, null, [], $fields);
        $this->assertEquals($fields, $metadata->getExtensionFields());
    }

    public function testCheckIsEntityExists()
    {
        $entityTableName = 'entity_table';
        $connectionName = 'default';
        $identifierField = 'blabla_id';
        $id = 1;
        $this->appResourceMock->expects($this->exactly(2))
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($this->connectionMock);
        $this->appResourceMock->expects($this->once())
            ->method('getTableName')
            ->with($entityTableName)
            ->willReturn($entityTableName);
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('from')
            ->with($entityTableName, [$identifierField])
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with($identifierField . ' = ?', $id)
            ->willReturnSelf();
        $this->selectMock->expects($this->once())->method('limit')->with(1)->willReturnSelf();
        $this->connectionMock->expects($this->once())->method('fetchOne')->with($this->selectMock)->willReturn($id);
        $metadata = new EntityMetadata(
            $this->appResourceMock,
            $entityTableName,
            $identifierField,
            null,
            null,
            $connectionName
        );
        $this->assertTrue($metadata->checkIsEntityExists($id));
    }
}
