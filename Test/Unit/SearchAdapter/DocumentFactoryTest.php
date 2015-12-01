<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\DocumentFactory;

class DocumentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\AggregationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregationFactory;

    /**
     * @var \Magento\Framework\Search\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadata;

    /**
     * @var \Magento\Framework\Search\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $document;

    /**
     * @var \Magento\Framework\Search\DocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentField;

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
    protected function setUp()
    {
        $this->entityMetadata = $this->getMockBuilder('Magento\Framework\Search\EntityMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->document = $this->getMockBuilder('Magento\Framework\Search\Document')
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentField = $this->getMockBuilder('\Magento\Framework\Search\DocumentField')
            ->disableOriginalConstructor()
            ->getMock();

        $this->instanceName = '\Magento\Framework\Search\Document';

        $this->model = new DocumentFactory(
            $this->objectManager,
            $this->entityMetadata
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
    }
}
