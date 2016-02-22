<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity\Action;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ReadEntityRow;
use Magento\Framework\Model\Entity\Action\ReadMain;

/**
 * Class ReadMainTest
 */
class ReadMainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readEntityRowMock;

    /**
     * @var ReadMain
     */
    protected $readMain;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readEntityRowMock = $this->getMockBuilder(ReadEntityRow::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readMain = new ReadMain(
            $this->metadataPoolMock,
            $this->readEntityRowMock
        );
    }

    public function testExecute()
    {
        $entityType = 'Type';
        $entity = new \stdClass();
        $entityData = ['name' => 'test'];
        $id = 1;
        $entityHydrator = $this->getMockBuilder(EntityHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($entityHydrator);
        $entityHydrator->expects($this->once())->method('hydrate')->with($entity, $entityData)->willReturn($entity);
        $this->readEntityRowMock->expects($this->once())
            ->method('execute')
            ->with($entityType, $id)
            ->willReturn($entityData);
        $this->assertEquals($entity, $this->readMain->execute($entityType, $entity, $id));
    }
}
