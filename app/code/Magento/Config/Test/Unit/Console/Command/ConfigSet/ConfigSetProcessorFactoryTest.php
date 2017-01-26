<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSet\DefaultProcessor;
use Magento\Config\Console\Command\ConfigSet\LockProcessor;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * {@inheritdoc}
 */
class ConfigSetProcessorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSetProcessorFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|Mock
     */
    private $objectManagerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ConfigSetProcessorFactory(
            $this->objectManagerMock,
            [
                ConfigSetProcessorFactory::TYPE_LOCK => LockProcessor::class,
                ConfigSetProcessorFactory::TYPE_DEFAULT => DefaultProcessor::class,
                'wrongType' => \stdClass::class,
            ]
        );
    }

    public function testCreate()
    {
        $processorMock = $this->getMockBuilder(ConfigSetProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(LockProcessor::class)
            ->willReturn($processorMock);

        $this->assertInstanceOf(
            ConfigSetProcessorInterface::class,
            $this->model->create(ConfigSetProcessorFactory::TYPE_LOCK)
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
            ->with(\stdClass::class)
            ->willReturn(new \stdClass());

        $this->model->create($type);
    }
}
