<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateInvalidArgument()
    {
        $className = 'class_name';
        $data = ['data'];

        $object = (new ObjectManager($this))->getObject(\Magento\Framework\DataObject::class);

        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManager */
        $objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->with($className, $data)
            ->will($this->returnValue($object));

        /** @var \Magento\Framework\View\Layout\ReaderFactory|\PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\View\Layout\ReaderFactory::class, ['objectManager' => $objectManager]);

        $this->setExpectedException(
            '\InvalidArgumentException',
            $className . ' doesn\'t implement \Magento\Framework\View\Layout\ReaderInterface'
        );
        $factory->create($className, $data);
    }

    public function testCreateValidArgument()
    {
        $className = 'class_name';
        $data = ['data'];

        /** @var \Magento\Framework\View\Layout\ReaderInterface|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMock(\Magento\Framework\View\Layout\ReaderInterface::class, [], [], '', false);

        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManager */
        $objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->with($className, $data)
            ->will($this->returnValue($object));

        /** @var \Magento\Framework\View\Layout\ReaderFactory|\PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\View\Layout\ReaderFactory::class, ['objectManager' => $objectManager]);

        $this->assertSame($object, $factory->create($className, $data));
    }
}
