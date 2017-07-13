<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorInterface;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\InteractiveCollector;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SimpleCollector;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;

class CollectorFactoryTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

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
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(SimpleCollector::class)
            ->willReturn($collectorMock);

        $this->assertInstanceOf(
            CollectorInterface::class,
            $this->model->create(CollectorFactory::TYPE_SIMPLE)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Class for type "dummyType" was not declared
     */
    public function testCreateNonExisted()
    {
        $this->model->create('dummyType');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage stdClass does not implement
     */
    public function testCreateWrongImplementation()
    {
        $type = 'wrongType';
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(stdClass::class)
            ->willReturn(new stdClass());

        $this->model->create($type);
    }
}
