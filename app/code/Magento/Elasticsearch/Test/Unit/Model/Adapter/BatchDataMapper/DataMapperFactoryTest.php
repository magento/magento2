<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\DataMapperFactory;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    /**
     * @var DataMapperFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|MockObject
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
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
     */
    public function testCreateEmpty()
    {
        $this->expectException(NoSuchEntityException::class);

        $this->model->create('');
    }

    /**
     * @return void
     */
    public function testCreateWrongType()
    {
        $this->expectException(NoSuchEntityException::class);

        $this->model->create('wrong');
    }

    /**
     * @return void
     */
    public function testCreateFailure()
    {
        $this->expectException(ConfigurationMismatchException::class);

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
            ->willReturn($this->getMockForAbstractClass(BatchDataMapperInterface::class));
        $this->assertInstanceOf(BatchDataMapperInterface::class, $this->model->create('product'));
    }
}
