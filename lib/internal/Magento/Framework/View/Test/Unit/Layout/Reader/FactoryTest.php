<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateInvalidArgument()
    {
        $className = 'class_name';
        $data = ['data'];

        $object = (new ObjectManager($this))->getObject(\Magento\Framework\DataObject::class);

        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManager */
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->with($className, $data)
            ->willReturn($object);

        /** @var \Magento\Framework\View\Layout\ReaderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\View\Layout\ReaderFactory::class, ['objectManager' => $objectManager]);

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage(
            $className . ' doesn\'t implement \Magento\Framework\View\Layout\ReaderInterface'
        );
        $factory->create($className, $data);
    }

    public function testCreateValidArgument()
    {
        $className = 'class_name';
        $data = ['data'];

        /** @var \Magento\Framework\View\Layout\ReaderInterface|\PHPUnit\Framework\MockObject\MockObject $object */
        $object = $this->createMock(\Magento\Framework\View\Layout\ReaderInterface::class);

        /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManager */
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->with($className, $data)
            ->willReturn($object);

        /** @var \Magento\Framework\View\Layout\ReaderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\View\Layout\ReaderFactory::class, ['objectManager' => $objectManager]);

        $this->assertSame($object, $factory->create($className, $data));
    }
}
