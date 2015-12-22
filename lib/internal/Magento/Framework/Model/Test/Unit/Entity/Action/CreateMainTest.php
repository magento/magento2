<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity\Action;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\CreateEntityRow;
use Magento\Framework\Model\Entity\Action\CreateMain;

/**
 * Class CreateMainTest
 */
class CreateMainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $createEntityRowMock;

    /**
     * @var CreateMain
     */
    protected $createMain;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createEntityRowMock = $this->getMockBuilder(CreateEntityRow::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createMain = new CreateMain(
            $this->metadataPoolMock,
            $this->createEntityRowMock
        );
    }

    public function testExecute()
    {
        $entityType = 'Type';
        $entity = new \stdClass();
        $entityData = ['name' => 'test'];
        $entityHydrator = $this->getMockBuilder(EntityHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($entityHydrator);
        $entityHydrator->expects($this->once())->method('extract')->with($entity)->willReturn([]);
        $entityHydrator->expects($this->once())->method('hydrate')->with($entity, $entityData)->willReturn($entity);
        $this->createEntityRowMock->expects($this->once())
            ->method('execute')
            ->with($entityType, $entityData)
            ->willReturn($entityData);
        $this->assertEquals($entity, $this->createMain->execute($entityType, $entity, $entityData));
    }
}
