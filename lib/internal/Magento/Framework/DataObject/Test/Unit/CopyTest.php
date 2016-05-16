<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DataObject\Test\Unit;

class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $copy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldsetConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $targetMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesFactoryMock;

    protected function setUp()
    {
        $this->fieldsetConfigMock = $this->getMock('Magento\Framework\DataObject\Copy\Config', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->sourceMock = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->targetMock = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->extensionAttributesFactoryMock =
            $this->getMock('Magento\Framework\Api\ExtensionAttributesFactory', [], [], '', false);
        $this->copy = new \Magento\Framework\DataObject\Copy(
            $this->eventManagerMock,
            $this->fieldsetConfigMock,
            $this->extensionAttributesFactoryMock
        );
    }

    public function testCopyFieldsetToTargetWhenFieldsetInputInvalid()
    {
        $this->fieldsetConfigMock->expects($this->never())->method('getFieldset');
        $this->assertEquals(
            null,
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', [], 'target')
        );
    }

    public function testCopyFieldsetToTargetWhenFieldIsNotExists()
    {
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue(null));
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->assertEquals(
            [$this->targetMock],
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, [$this->targetMock])
        );
    }

    public function testCopyFieldsetToTargetWhenFieldExists()
    {
        $fields['code']['node']['aspect'] = [];
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));

        $eventName = sprintf('core_copy_fieldset_%s_%s', 'fieldset', 'aspect');
        $data = [
            'target' => new \Magento\Framework\DataObject([$this->targetMock]),
            'source' => $this->sourceMock,
            'root'   => 'global',
        ];
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $data);
        $this->assertEquals(
            [$this->targetMock],
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, [$this->targetMock])
        );
    }

    public function testCopyFieldsetToTargetWhenTargetNotArray()
    {
        $fields['code']['aspect'] = 'value';
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));

        $this->sourceMock
            ->expects($this->once())
            ->method('getDataUsingMethod')
            ->with('code')
            ->will($this->returnValue('value'));

        $this->targetMock
            ->expects($this->once())
            ->method('setDataUsingMethod')
            ->with('value')
            ->will($this->returnSelf());
        $eventName = sprintf('core_copy_fieldset_%s_%s', 'fieldset', 'aspect');
        $data = [
            'target' => $this->targetMock,
            'source' => $this->sourceMock,
            'root'   => 'global',
        ];
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $data);
        $this->assertEquals(
            $this->targetMock,
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, $this->targetMock)
        );
    }

    public function testGetCopyFieldsetToTargetWhenTargetIsArray()
    {
        $fields['code']['aspect'] = 'value';
        $target['code'] = [];
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));

        $this->sourceMock
            ->expects($this->once())
            ->method('getDataUsingMethod')
            ->with('code')
            ->will($this->returnValue('value'));

        $this->targetMock
            ->expects($this->never())
            ->method('setDataUsingMethod');
        $eventName = sprintf('core_copy_fieldset_%s_%s', 'fieldset', 'aspect');
        $newTarget = [
            'code' => [],
            'value' => 'value',
        ];
        $data = [
            'target' => new \Magento\Framework\DataObject($newTarget),
            'source' => $this->sourceMock,
            'root'   => 'global',
        ];
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $data);
        $this->assertEquals(
            $newTarget,
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, $target)
        );
    }

    public function testGetCopyFieldsetToTargetWhenTargetIsExtensibleDataInterface()
    {
        $fields['code']['aspect'] = '*';
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));

        $sourceMock = $this->getMock(
            'Magento\Framework\Api\ExtensibleDataInterface',
            [
                'getExtensionAttributes', 'getCode'
            ],
            [],
            '',
            false
        );
        $targetMock = $this->getMock(
            'Magento\Framework\Api\ExtensibleDataInterface',
            [
                'getExtensionAttributes',
                'setCode',
                'setExtensionAttributes'
            ],
            [],
            '',
            false
        );

        $sourceMock
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturnSelf();
        $sourceMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn('code');

        $targetMock
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturnSelf();
        $targetMock
            ->expects($this->any())
            ->method('setExtensionAttributes')
            ->willReturnSelf();
        $targetMock
            ->expects($this->once())
            ->method('setCode')
            ->with('code');

        $this->eventManagerMock->expects($this->once())->method('dispatch');
        $result = $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $sourceMock, $targetMock);
        $this->assertEquals($result, $targetMock);
    }

    public function testGetCopyFieldsetToTargetWhenTargetIsAbstractSimpleObject()
    {
        $fields['code']['aspect'] = '*';
        $source['code'] = 'code';
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));

        $sourceMock = $this->getMock(
            'Magento\Framework\Api\AbstractSimpleObject',
            [
                '__toArray'
            ],
            [],
            '',
            false
        );
        $targetMock = $this->getMock(
            'Magento\Framework\Api\AbstractSimpleObject',
            [
                'setData'
            ],
            [],
            '',
            false
        );

        $sourceMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn($source);

        $targetMock
            ->expects($this->once())
            ->method('setData')
            ->with('code', 'code');

        $this->eventManagerMock->expects($this->once())->method('dispatch');
        $result = $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $sourceMock, $targetMock);
        $this->assertEquals($result, $targetMock);
    }

    public function testGetDataFromFieldsetWhenSourceIsInvalid()
    {
        $this->fieldsetConfigMock->expects($this->never())->method('getFieldset');
        $this->assertNull($this->copy->getDataFromFieldset('fieldset', 'aspect', 'source'));
    }

    public function testGetDataFromFieldsetWhenFieldsetDoesNotExist()
    {
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue(null));
        $this->sourceMock
            ->expects($this->never())
            ->method('getDataUsingMethod');
        $this->assertNull($this->copy->getDataFromFieldset('fieldset', 'aspect', $this->sourceMock));
    }

    public function testGetDataFromFieldsetWhenFieldExists()
    {
        $fields['code']['aspect'] = 'value';
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));
        $this->sourceMock
            ->expects($this->once())
            ->method('getDataUsingMethod')
            ->with('code')
            ->will($this->returnValue('value'));

        $this->assertEquals(
            ['value' => 'value'],
            $this->copy->getDataFromFieldset('fieldset', 'aspect', $this->sourceMock)
        );
    }

    public function testGetDataFromFieldsetWhenFieldDoesNotExists()
    {
        $fields['code']['aspect'] = [];
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));
        $this->sourceMock
            ->expects($this->never())
            ->method('getDataUsingMethod');

        $this->assertEquals(
            [],
            $this->copy->getDataFromFieldset('fieldset', 'aspect', $this->sourceMock)
        );
    }
}
