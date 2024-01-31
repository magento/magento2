<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Block\Adminhtml\Attribute;

use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Config;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PropertyLockerTest extends TestCase
{
    /** @var PropertyLocker */
    protected $object;

    /** @var AbstractAttribute|MockObject */
    protected $attributeMock;

    /** @var Form|MockObject */
    protected $formMock;

    /** @var Config|MockObject */
    protected $attributeConfigMock;

    protected function setUp(): void
    {
        $this->attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder(Registry::class)
            ->onlyMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock->expects($this->atLeastOnce())->method('registry')->willReturn($this->attributeMock);

        $this->attributeConfigMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getLockedFields'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->formMock = $this->getMockBuilder(Form::class)
            ->onlyMethods(['getElement'])
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

        $elementMock = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['setDisabled'])
            ->onlyMethods(['setReadonly'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $elementMock->expects($this->exactly(2))->method('setDisabled');
        $elementMock->expects($this->exactly(2))->method('setReadonly');
        $this->formMock->expects($this->exactly(2))->method('getElement')->willReturn($elementMock);
        $this->object->lock($this->formMock);
    }
}
