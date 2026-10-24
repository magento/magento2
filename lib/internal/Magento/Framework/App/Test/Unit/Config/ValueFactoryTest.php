<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase;

class ValueFactoryTest extends AbstractFactoryTestCase
{
    protected function setUp(): void
    {
        $this->instanceClassName = ValueInterface::class;
        $this->factoryClassName = ValueFactory::class;
        parent::setUp();
    }

    public function testCreateWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn('somethingElse');
        $this->factory->create();
    }
}
