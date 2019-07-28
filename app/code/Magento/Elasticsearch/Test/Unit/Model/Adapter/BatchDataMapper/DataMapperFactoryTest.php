<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\DataMapperFactory;
use Magento\Framework\ObjectManagerInterface;

class DataMapperFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataMapperFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var string[]
     */
    private $dataMappers;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataMappers = [
            'product' => 'productDataMapper',
        ];
        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            DataMapperFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'dataMappers' => $this->dataMappers
            ]
        );
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCreateEmpty()
    {
        $this->model->create('');
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCreateWrongType()
    {
        $this->model->create('wrong');
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     */
    public function testCreateFailure()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(new \stdClass());
        $this->model->create('product');
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(BatchDataMapperInterface::class));
        $this->assertInstanceOf(BatchDataMapperInterface::class, $this->model->create('product'));
    }
}
