<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\ReaderFactory;
use Magento\Framework\View\Layout\ReaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
            ->willReturn($object);

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
            ->willReturn($object);

        /** @var ReaderFactory|MockObject $factory */
        $factory = (new ObjectManager($this))
            ->getObject(ReaderFactory::class, ['objectManager' => $objectManager]);

        $this->assertSame($object, $factory->create($className, $data));
    }
}
