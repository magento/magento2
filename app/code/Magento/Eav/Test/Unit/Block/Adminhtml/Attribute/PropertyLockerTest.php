<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Block\Adminhtml\Attribute;

use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;

class PropertyLockerTest extends \PHPUnit_Framework_TestCase
{
    /** @var PropertyLocker */
    protected $object;

    /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMock;

    /** @var \Magento\Framework\Data\Form|\PHPUnit_Framework_MockObject_MockObject */
    protected $formMock;

    /** @var \Magento\Eav\Model\Entity\Attribute\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeConfigMock;

    protected function setUp()
    {
        $this->attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('\Magento\Framework\Registry')
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock->expects($this->atLeastOnce())->method('registry')->willReturn($this->attributeMock);

        $this->attributeConfigMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Config')
            ->setMethods(['getLockedFields'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->formMock = $this->getMockBuilder('Magento\Framework\Data\Form')
            ->setMethods(['getElement'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new PropertyLocker($registryMock, $this->attributeConfigMock);
    }

    /**
     * @covers \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker::lock
     */
    public function testLock()
    {
        $lockedFields = [
            'is_searchable' => 'is_searchable',
            'is_filterable' => 'is_filterable'
        ];
        $this->attributeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->attributeConfigMock->expects($this->once())->method('getLockedFields')->willReturn($lockedFields);

        $elementMock = $this->getMockBuilder('\Magento\Framework\Data\Form\Element\AbstractElement')
            ->setMethods(['setDisabled', 'setReadonly'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $elementMock->expects($this->exactly(2))->method('setDisabled');
        $elementMock->expects($this->exactly(2))->method('setReadonly');
        $this->formMock->expects($this->exactly(2))->method('getElement')->willReturn($elementMock);
        $this->object->lock($this->formMock);
    }
}
