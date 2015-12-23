<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity\Action;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\UpdateEntityRow;
use Magento\Framework\Model\Entity\Action\UpdateMain;

/**
 * Class UpdateMainTest
 */
class UpdateMainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateEntityRowMock;

    /**
     * @var UpdateMain
     */
    protected $updateMain;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateEntityRowMock = $this->getMockBuilder(UpdateEntityRow::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateMain = new UpdateMain(
            $this->metadataPoolMock,
            $this->updateEntityRowMock
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
        $this->updateEntityRowMock->expects($this->once())
            ->method('execute')
            ->with($entityType, $entityData)
            ->willReturn($entityData);
        $this->assertEquals($entity, $this->updateMain->execute($entityType, $entity, $entityData));
    }
}
