<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\Query
     */
    private $model;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->resource = $this->getMockBuilder('Magento\Search\Model\ResourceModel\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject('Magento\Search\Model\Query', ['resource' => $this->resource]);
    }

    public function testSaveNumResults()
    {
        $this->resource->expects($this->once())
            ->method('saveNumResults')
            ->with($this->model);

        $result = $this->model->saveNumResults(30);

        $this->assertEquals($this->model, $result);
        $this->assertEquals(30, $this->model->getNumResults());
    }

    public function testSaveIncrementalPopularity()
    {
        $this->resource->expects($this->once())
            ->method('saveIncrementalPopularity')
            ->with($this->model);

        $result = $this->model->saveIncrementalPopularity();

        $this->assertEquals($this->model, $result);
    }
}
