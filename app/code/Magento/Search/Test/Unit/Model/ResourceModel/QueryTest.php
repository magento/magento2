<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class QueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Model\ResourceModel\Query
     */
    private $model;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adapter;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);

        $context = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getResources')
            ->willReturn($resource);

        $this->model = $objectManager->getObject(
            \Magento\Search\Model\ResourceModel\Query::class,
            ['context' => $context]
        );
    }

    public function testSaveIncrementalPopularity()
    {
        /** @var \Magento\Search\Model\Query|\PHPUnit\Framework\MockObject\MockObject $model */
        $model = $this->getMockBuilder(\Magento\Search\Model\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $model->expects($this->any())
            ->method('getQueryText')
            ->willReturn('queryText');

        $this->adapter->expects($this->once())
            ->method('insertOnDuplicate');

        $this->model->saveIncrementalPopularity($model);
    }

    public function testSaveNumResults()
    {
        /** @var \Magento\Search\Model\Query|\PHPUnit\Framework\MockObject\MockObject $model */
        $model = $this->getMockBuilder(\Magento\Search\Model\Query::class)
            ->setMethods(['getNumResults', 'getStoreId', 'getQueryText'])
            ->disableOriginalConstructor()
            ->getMock();
        $model->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $model->expects($this->any())
            ->method('getQueryText')
            ->willReturn('queryText');
        $model->expects($this->any())
            ->method('getNumResults')
            ->willReturn(30);

        $this->adapter->expects($this->once())
            ->method('insertOnDuplicate');

        $this->model->saveNumResults($model);
    }
}
