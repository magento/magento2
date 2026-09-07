<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorInterface;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\InteractiveCollector;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SimpleCollector;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class CollectorFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var CollectorFactory
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->model = new CollectorFactory(
            $this->objectManagerMock,
            [
                CollectorFactory::TYPE_SIMPLE => SimpleCollector::class,
                CollectorFactory::TYPE_INTERACTIVE => InteractiveCollector::class,
                'wrongType' => stdClass::class,
            ]
        );
    }

    public function testCreate()
    {
        $collectorMock = $this->createMock(CollectorInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(SimpleCollector::class)
            ->willReturn($collectorMock);

        $this->assertInstanceOf(
            CollectorInterface::class,
            $this->model->create(CollectorFactory::TYPE_SIMPLE)
        );
    }

    public function testCreateNonExisted()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The class for "dummyType" type wasn\'t declared. Enter the class and try again.'
        );
        $this->model->create('dummyType');
    }

    public function testCreateWrongImplementation()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('stdClass does not implement');
        $type = 'wrongType';
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(stdClass::class)
            ->willReturn(new stdClass());

        $this->model->create($type);
    }
}
