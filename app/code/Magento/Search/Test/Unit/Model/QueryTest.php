<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /**
     * @var Query
     */
    private $model;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query|MockObject
     */
    private $resource;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resource = $this->getMockBuilder(\Magento\Search\Model\ResourceModel\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(Query::class, ['resource' => $this->resource]);
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
