<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\DataMapperFactory;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\DataMapperResolver;

class DataMapperResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataMapperResolver
     */
    private $model;

    /**
     * @var DataMapperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMapperFactoryMock;

    /**
     * @var BatchDataMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMapperEntity;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->dataMapperFactoryMock = $this->getMockBuilder(DataMapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataMapperEntity = $this->getMockBuilder(BatchDataMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManagerHelper($this))->getObject(
            \Magento\Elasticsearch\Model\Adapter\BatchDataMapper\DataMapperResolver::class,
            [
                'dataMapperFactory' => $this->dataMapperFactoryMock
            ]
        );
    }

    public function testMapWithDefaultEntityType()
    {
        $this->dataMapperEntity->expects($this->once())->method('map')->withAnyParameters();
        $this->dataMapperFactoryMock->expects($this->once())->method('create')
            ->with('product')
            ->willReturn($this->dataMapperEntity);

        $this->model->map(['data'], 1, []);
    }

    public function testMapWithSpecifiedEntityType()
    {
        $this->dataMapperEntity->expects($this->once())->method('map')->withAnyParameters();
        $this->dataMapperFactoryMock->expects($this->once())->method('create')
            ->with('specific-type')
            ->willReturn($this->dataMapperEntity);

        $this->model->map(['data'], 1, ['entityType' => 'specific-type']);
    }
}
