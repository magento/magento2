<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Operation\Write;

use Magento\Framework\Model\Entity\Action\DeleteMain;
use Magento\Framework\Model\Entity\Action\DeleteExtension;
use Magento\Framework\Model\Entity\Action\DeleteRelation;
use Magento\Framework\Model\Operation\Write\Delete;

/**
 * Class DeleteTest
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeleteMain|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteMainMock;

    /**
     * @var DeleteExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteExtensionMock;

    /**
     * @var DeleteRelation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteRelationMock;

    /**
     * @var Delete
     */
    protected $delete;

    protected function setUp()
    {
        $this->deleteMainMock = $this->getMockBuilder(DeleteMain::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteExtensionMock = $this->getMockBuilder(DeleteExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteRelationMock = $this->getMockBuilder(DeleteRelation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->delete = new Delete(
            $this->deleteMainMock,
            $this->deleteExtensionMock,
            $this->deleteRelationMock
        );
    }

    public function testExecute()
    {
        $entityType = 'SomeNameSpace\SomeClassName';
        $entity = ['name' => 'test'];
        $this->deleteMainMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entity
        );
        $this->deleteExtensionMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entity
        );
        $this->deleteRelationMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entity
        );
        $this->assertTrue($this->delete->execute($entityType, $entity));
    }
}
