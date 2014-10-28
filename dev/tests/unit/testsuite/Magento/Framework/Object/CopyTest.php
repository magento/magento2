<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Object;


class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy
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


    protected function setUp()
    {
        $this->fieldsetConfigMock = $this->getMock('Magento\Framework\Object\Copy\Config', array(), array(), '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->sourceMock = $this->getMock('Magento\Framework\Object', array(), array(), '', false);
        $this->targetMock = $this->getMock('Magento\Framework\Object', array(), array(), '', false);
        $this->copy = new \Magento\Framework\Object\Copy(
            $this->eventManagerMock,
            $this->fieldsetConfigMock
        );
    }

    public function testCopyFieldsetToTargetWhenFieldsetInputInvalid()
    {
        $this->fieldsetConfigMock->expects($this->never())->method('getFieldset');
        $this->assertEquals(
            null,
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', array(), 'target')
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
            array($this->targetMock),
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, array($this->targetMock))
        );
    }

    public function testCopyFieldsetToTargetWhenFieldExists()
    {
        $fields['code']['node']['aspect'] = array();
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));

        $eventName = sprintf('core_copy_fieldset_%s_%s', 'fieldset', 'aspect');
        $data = array(
            'target' => array($this->targetMock),
            'source' => $this->sourceMock,
            'root'   => 'global'
        );
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $data);
        $this->assertEquals(
            array($this->targetMock),
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, array($this->targetMock))
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
        $eventName = sprintf('core_copy_fieldset_%s_%s', 'fieldset', 'aspect');;
        $data = array(
            'target' => $this->targetMock,
            'source' => $this->sourceMock,
            'root'   => 'global'
        );
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $data);
        $this->assertEquals(
            $this->targetMock,
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, $this->targetMock)
        );
    }

    public function testGetCopyFieldsetToTargetWhenTargetIsArray()
    {
        $fields['code']['aspect'] = 'value';
        $target['code'] = array();
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
        $newTarget = array(
            'code' => array(),
            'value' => 'value'
        );
        $data = array(
            'target' => $newTarget,
            'source' => $this->sourceMock,
            'root'   => 'global'
        );
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $data);
        $this->assertEquals(
            $newTarget,
            $this->copy->copyFieldsetToTarget('fieldset', 'aspect', $this->sourceMock, $target)
        );
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
            array('value' => 'value'),
            $this->copy->getDataFromFieldset('fieldset', 'aspect', $this->sourceMock)
        );
    }


    public function testGetDataFromFieldsetWhenFieldDoesNotExists()
    {
        $fields['code']['aspect'] = array();
        $this->fieldsetConfigMock
            ->expects($this->once())
            ->method('getFieldset')
            ->with('fieldset', 'global')
            ->will($this->returnValue($fields));
        $this->sourceMock
            ->expects($this->never())
            ->method('getDataUsingMethod');

        $this->assertEquals(
            array(),
            $this->copy->getDataFromFieldset('fieldset', 'aspect', $this->sourceMock)
        );
    }
}
