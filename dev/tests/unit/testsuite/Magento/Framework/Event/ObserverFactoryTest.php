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

namespace Magento\Framework\Event;

/**
 * Class ConfigTest
 *
 * @package Magento\Framework\Event
 */
class ObserverFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ObserverFactory
     */
    protected $observerFactory;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['get', 'create'],
            [],
            '',
            false,
            false
        );
        $this->observerFactory = new ObserverFactory($this->objectManagerMock);
    }

    public function testGet()
    {
        $className = 'Magento\Class';
        $observerMock = $this->getMock('Magento\Observer', [], [], '', false, false);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($className)
            ->will($this->returnValue($observerMock));

        $result = $this->observerFactory->get($className);
        $this->assertEquals($observerMock, $result);
    }

    public function testCreate()
    {
        $className = 'Magento\Class';
        $observerMock = $this->getMock('Magento\Observer', [], [], '', false, false);
        $arguments = ['arg1', 'arg2'];

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $this->equalTo($arguments))
            ->will($this->returnValue($observerMock));

        $result = $this->observerFactory->create($className, $arguments);
        $this->assertEquals($observerMock, $result);
    }
} 
