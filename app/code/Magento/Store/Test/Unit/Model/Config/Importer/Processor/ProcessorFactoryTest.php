<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Config\Importer\Processor\Create;
use Magento\Store\Model\Config\Importer\Processor\Delete;
use Magento\Store\Model\Config\Importer\Processor\ProcessorFactory;
use Magento\Store\Model\Config\Importer\Processor\ProcessorInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class ProcessorFactoryTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
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

    /**
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage Class for type "dummyType" was not declared
     */
    public function testCreateNonExisted()
    {
        $this->model->create('dummyType');
    }

    /**
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage stdClass should implement
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
