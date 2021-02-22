<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\DocumentFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DocumentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\AggregationFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aggregationFactory;

    /**
     * @var \Magento\Framework\Search\EntityMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityMetadata;

    /**
     * Instance name
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->entityMetadata = $this->getMockBuilder(\Magento\Framework\Search\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->instanceName = \Magento\Framework\Api\Search\Document::class;

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\DocumentFactory::class,
            [
                'objectManager' => $this->objectManager,
                'entityMetadata' => $this->entityMetadata
            ]
        );
    }

    /**
     *  Test Create method
     */
    public function testCreate()
    {
        $documents = [
            '_id' => 2,
            '_score' => 1.00,
            '_index' => 'indexName',
            '_type' => 'product',
        ];

        $this->entityMetadata->expects($this->once())
            ->method('getEntityId')
            ->willReturn('_id');

        $result = $this->model->create($documents);
        $this->assertInstanceOf($this->instanceName, $result);
        $this->assertEquals($documents['_id'], $result->getId());
        $this->assertEquals($documents['_score'], $result->getCustomAttribute('score')->getValue());
    }
}
