<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Config\Importer\Processor\Create;
use Magento\Store\Model\Config\Importer\Processor\Delete;
use Magento\Store\Model\Config\Importer\Processor\ProcessorFactory;
use Magento\Store\Model\Config\Importer\Processor\ProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 */
class ProcessorFactoryTest extends TestCase
{
    /**
     * @var ProcessorFactory
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

        $this->model = new ProcessorFactory(
            $this->objectManagerMock,
            [
                ProcessorFactory::TYPE_CREATE => Create::class,
                ProcessorFactory::TYPE_DELETE => Delete::class,
                'wrongType' => \stdClass::class,
            ]
        );
    }

    public function testCreate()
    {
        $processorMock = $this->getMockBuilder(ProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Create::class)
            ->willReturn($processorMock);

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->model->create(ProcessorFactory::TYPE_CREATE)
        );
    }

    public function testCreateNonExisted()
    {
        $this->expectException(ConfigurationMismatchException::class);
        $this->expectExceptionMessage(
            'The class for "dummyType" type wasn\'t declared. Enter the class and try again.'
        );
        $this->model->create('dummyType');
    }

    public function testCreateWrongImplementation()
    {
        $this->expectException(ConfigurationMismatchException::class);
        $this->expectExceptionMessage('stdClass should implement');
        $type = 'wrongType';
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\stdClass::class)
            ->willReturn(new \stdClass());

        $this->model->create($type);
    }
}
