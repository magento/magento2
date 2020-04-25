<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use PHPUnit\Framework\TestCase;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Layout\ReaderFactory;
use Magento\Framework\View\Layout\ReaderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FactoryTest extends TestCase
{
    public function testCreateInvalidArgument()
    {
        $className = 'class_name';
        $data = ['data'];

        $object = (new ObjectManager($this))->getObject(DataObject::class);

        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->with($className, $data)
            ->will($this->returnValue($object));

        /** @var ReaderFactory|MockObject $factory */
        $factory = (new ObjectManager($this))
            ->getObject(ReaderFactory::class, ['objectManager' => $objectManager]);

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

        /** @var ReaderInterface|MockObject $object */
        $object = $this->createMock(ReaderInterface::class);

        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())->method('create')->with($className, $data)
            ->will($this->returnValue($object));

        /** @var ReaderFactory|MockObject $factory */
        $factory = (new ObjectManager($this))
            ->getObject(ReaderFactory::class, ['objectManager' => $objectManager]);

        $this->assertSame($object, $factory->create($className, $data));
    }
}
