<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\DataMapper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\DataMapper\DataMapperResolver;
use Magento\Framework\ObjectManagerInterface;

class DataMapperResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataMapperResolver
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
     * @var DataMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMapperEntity;

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
        $this->dataMapperEntity = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\DataMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataMappers = [
            'product' => 'productDataMapper',
        ];
        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\DataMapper\DataMapperResolver::class,
            [
                'objectManager' => $this->objectManagerMock,
                'dataMappers' => $this->dataMappers
            ]
        );
    }

    /**
     * Test map() with Exception
     * @return void
     * @expectedException \Exception
     */
    public function testMapEmpty()
    {
        $this->model->map(1, [], 1, ['entityType' => '']);
    }

    /**
     * Test map() with Exception
     * @return void
     * @expectedException \LogicException
     */
    public function testMapWrongType()
    {
        $this->model->map(1, [], 1, ['entityType' => 'error']);
    }

    /**
     * Test map() with Exception
     * @return void
     * @expectedException \InvalidArgumentException
     */
    public function testMapFailure()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(false);
        $this->model->map(1, [], 1, ['entityType' => 'product']);
    }

    /**
     * Test map() method
     * @return void
     */
    public function testMap()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dataMapperEntity);
        $this->model->map(1, [], 1, ['entityType' => 'product']);
    }
}
