<?php
/** 
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Resource\Entity\Attribute;
 
class SetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    protected function setUp()
    {
        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->model = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set',
            [
                'beginTransaction',
                '_getWriteAdapter',
                'getMainTable',
                'getIdFieldName',
                '_afterDelete',
                'commit',
                'rollBack',
                '__wakeup'
            ],
            [
                $this->getMock('Magento\Framework\App\Resource', [], [], '', false),
                $this->getMock('Magento\Eav\Model\Resource\Entity\Attribute\GroupFactory', [], [], '', false),
                $this->eavConfigMock
            ],
            '',
            true
        );
        $this->typeMock = $this->getMock('\Magento\Eav\Model\Entity\Type', [], [], '', false);
        $this->objectMock = $this->getMock(
            'Magento\Framework\Model\AbstractModel',
            [
                'getEntityTypeId',
                'getAttributeSetId',
                'beforeDelete',
                'getId',
                'isDeleted',
                'afterDelete',
                'afterDeleteCommit',
                '__wakeup'
            ],
            [],
            '',
            false
        );

    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Default attribute set can not be deleted
     */
    public function testBeforeDeleteStateException()
    {
        $this->objectMock->expects($this->once())->method('getEntityTypeId')->willReturn(665);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(665)->willReturn($this->typeMock);
        $this->typeMock->expects($this->once())->method('getDefaultAttributeSetId')->willReturn(4);
        $this->objectMock->expects($this->once())->method('getAttributeSetId')->willReturn(4);

        $this->model->delete($this->objectMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage test exception
     */
    public function testBeforeDelete()
    {
        $this->objectMock->expects($this->once())->method('getEntityTypeId')->willReturn(665);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(665)->willReturn($this->typeMock);
        $this->typeMock->expects($this->once())->method('getDefaultAttributeSetId')->willReturn(4);
        $this->objectMock->expects($this->once())->method('getAttributeSetId')->willReturn(5);
        $this->model->expects($this->once())
            ->method('_getWriteAdapter')
            ->willThrowException(new \Exception('test exception'));

        $this->model->delete($this->objectMock);
    }
}
