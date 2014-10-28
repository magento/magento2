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

namespace Magento\Framework\Message;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;


    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->factory = new \Magento\Framework\Message\Factory(
            $this->objectManagerMock
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong message type
     */
    public function testCreateWithWrongTypeException()
    {
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->factory->create('type', 'text');
    }

    public function testCreateWithWrongInterfaceImplementation()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Magento\Framework\Message\Error doesn\'t implement \Magento\Framework\Message\MessageInterface'
        );
        $messageMock = new \stdClass();
        $type = 'error';
        $className = 'Magento\\Framework\\Message\\' . ucfirst($type);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($className, array('text' => 'text'))
            ->will($this->returnValue($messageMock));
        $this->factory->create($type, 'text');
    }

    public function testSuccessfulCreateMessage()
    {
        $messageMock = $this->getMock('Magento\Framework\Message\Success', array(), array(), '', false);
        $type = 'success';
        $className = 'Magento\\Framework\\Message\\' . ucfirst($type);
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($className, array('text' => 'text'))
            ->will($this->returnValue($messageMock));
        $this->assertEquals($messageMock, $this->factory->create($type, 'text'));
    }
}
