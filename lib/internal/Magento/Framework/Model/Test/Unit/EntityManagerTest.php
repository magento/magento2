<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit;

/**
 * Unit test for EntityManager class.
 */
class EntityManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\EntityManager
     */
    protected $subject;

    /**
     * @var \Magento\Framework\Model\Entity\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Magento\Framework\Model\Entity\EntityHydrator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $hydrator;

    /**
     * @var \Magento\Framework\Model\Entity\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var \Magento\Eav\Model\Entity\AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractEntity;

    /**
     * @var \Magento\Framework\Model\Operation\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeOperation;

    /**
     * @var \Magento\Framework\Model\OrchestratorPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orchestratorPool;

    protected function setUp()
    {
        $this->markTestSkipped('Due to MAGETWO-48956');
        $this->metadata = $this->getMock(
            'Magento\Framework\Model\Entity\EntityMetadata',
            [],
            [],
            '',
            false
        );

        $this->metadata->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn('identifier');

        $this->hydrator = $this->getMock(
            'Magento\Framework\Model\Entity\EntityHydrator',
            [],
            [],
            '',
            false
        );

        $this->metadataPool = $this->getMock(
            'Magento\Framework\Model\Entity\MetadataPool',
            [],
            [],
            '',
            false
        );

        $this->metadataPool->expects($this->any())
            ->method('getHydrator')
            ->with('Test\Entity\Type')
            ->willReturn($this->hydrator);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with('Test\Entity\Type')
            ->willReturn($this->metadata);

        $this->abstractEntity = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\AbstractEntity',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->writeOperation = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Operation\WriteInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->orchestratorPool = $this->getMock(
            'Magento\Framework\Model\OrchestratorPool',
            [],
            [],
            '',
            false
        );

        $this->subject = new \Magento\Framework\Model\EntityManager(
            $this->orchestratorPool,
            $this->metadataPool
        );
    }

    public function testLoad()
    {
        $readOperation = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Operation\ReadInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $readOperation->expects($this->once())
            ->method('execute')
            ->with('Test\Entity\Type', $this->abstractEntity, '100000001')
            ->willReturn($this->abstractEntity);

        $this->orchestratorPool->expects($this->once())
            ->method('getReadOperation')
            ->with('Test\Entity\Type')
            ->willReturn($readOperation);

        $result = $this->subject->load('Test\Entity\Type', $this->abstractEntity, '100000001');

        $this->assertEquals($this->abstractEntity, $result);
    }

    public function testSaveUpdateExisting()
    {
        $this->hydrator->expects($this->once())
            ->method('extract')
            ->with($this->abstractEntity)
            ->willReturn([
                'identifier' => '100000001',
                'test_field_1' => 'test_value_1',
                'test_filed_2' => 'test_field_2'
            ]);

        $this->metadata->expects($this->once())
            ->method('checkIsEntityExists')
            ->with('100000001')
            ->willReturn(true);

        $this->orchestratorPool->expects($this->once())
            ->method('getWriteOperation')
            ->with('Test\Entity\Type', 'update')
            ->willReturn($this->writeOperation);

        $this->writeOperation->expects($this->once())
            ->method('execute')
            ->with('Test\Entity\Type', $this->abstractEntity)
            ->willReturn($this->abstractEntity);

        $result = $this->subject->save('Test\Entity\Type', $this->abstractEntity);

        $this->assertEquals($this->abstractEntity, $result);
    }

    public function testSaveUpdateNotExisting()
    {
        $this->hydrator->expects($this->once())
            ->method('extract')
            ->with($this->abstractEntity)
            ->willReturn([
                'identifier' => '100000001',
                'test_field_1' => 'test_value_1',
                'test_filed_2' => 'test_field_2'
            ]);

        $this->metadata->expects($this->once())
            ->method('checkIsEntityExists')
            ->with('100000001')
            ->willReturn(false);

        $this->orchestratorPool->expects($this->once())
            ->method('getWriteOperation')
            ->with('Test\Entity\Type', 'create')
            ->willReturn($this->writeOperation);

        $this->writeOperation->expects($this->once())
            ->method('execute')
            ->with('Test\Entity\Type', $this->abstractEntity)
            ->willReturn($this->abstractEntity);

        $result = $this->subject->save('Test\Entity\Type', $this->abstractEntity);

        $this->assertEquals($this->abstractEntity, $result);
    }

    public function testSaveCreate()
    {
        $this->hydrator->expects($this->once())
            ->method('extract')
            ->with($this->abstractEntity)
            ->willReturn([
                'test_field_1' => 'test_value_1',
                'test_filed_2' => 'test_field_2'
            ]);

        $this->metadata->expects($this->never())
            ->method('checkIsEntityExists');

        $this->orchestratorPool->expects($this->once())
            ->method('getWriteOperation')
            ->with('Test\Entity\Type', 'create')
            ->willReturn($this->writeOperation);

        $this->writeOperation->expects($this->once())
            ->method('execute')
            ->with('Test\Entity\Type', $this->abstractEntity)
            ->willReturn($this->abstractEntity);

        $result = $this->subject->save('Test\Entity\Type', $this->abstractEntity);

        $this->assertEquals($this->abstractEntity, $result);
    }

    public function testDelete()
    {
        $this->orchestratorPool->expects($this->once())
            ->method('getWriteOperation')
            ->with('Test\Entity\Type', 'delete')
            ->willReturn($this->writeOperation);

        $this->writeOperation->expects($this->once())
            ->method('execute')
            ->with('Test\Entity\Type', $this->abstractEntity)
            ->willReturn(true);

        $this->assertTrue(
            $this->subject->delete('Test\Entity\Type', $this->abstractEntity)
        );
    }
}
