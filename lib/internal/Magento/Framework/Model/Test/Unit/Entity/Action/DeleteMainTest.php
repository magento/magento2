<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity\Action;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow;
use Magento\Framework\Model\Entity\Action\DeleteMain;

/**
 * Class DeleteMainTest
 */
class DeleteMainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteEntityRowMock;

    /**
     * @var DeleteMain
     */
    protected $deleteMain;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteEntityRowMock = $this->getMockBuilder(DeleteEntityRow::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteMain = new DeleteMain(
            $this->metadataPoolMock,
            $this->deleteEntityRowMock
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
        $entityHydrator->expects($this->once())->method('extract')->with($entity)->willReturn($entityData);
        $this->deleteEntityRowMock->expects($this->once())
            ->method('execute')
            ->with($entityType, $entityData)
            ->willReturn($entity);
        $this->assertEquals($entity, $this->deleteMain->execute($entityType, $entity));
    }
}
