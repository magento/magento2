<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSet\DefaultProcessor;
use Magento\Config\Console\Command\ConfigSet\LockProcessor;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;

/**
 * Test for ConfigSetProcessorFactory.
 *
 * @see ConfigSetProcessorFactory
 */
class ConfigSetProcessorFactoryTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ConfigSetProcessorFactory(
            $this->objectManagerMock,
            [
                ConfigSetProcessorFactory::TYPE_LOCK_ENV => LockProcessor::class,
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
            $this->model->create(ConfigSetProcessorFactory::TYPE_LOCK_ENV)
        );
    }

    /**
     */
    public function testCreateNonExisted()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The class for "dummyType" type wasn\'t declared. Enter the class and try again.');

        $this->model->create('dummyType');
    }

    /**
     */
    public function testCreateWrongImplementation()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('stdClass should implement');

        $type = 'wrongType';
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\stdClass::class)
            ->willReturn(new \stdClass());

        $this->model->create($type);
    }
}
