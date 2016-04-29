<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\DocumentFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
        $this->entityMetadata = $this->getMockBuilder(\Magento\Framework\Search\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->document = $this->getMockBuilder(\Magento\Framework\Search\Document::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentField = $this->getMockBuilder(\Magento\Framework\Search\DocumentField::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instanceName = \Magento\Framework\Search\Document::class;

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
        /**
         * @param string $class
         *
         * @return \Magento\Framework\Search\Document|\Magento\Framework\Search\DocumentField|null|
         *     \PHPUnit_Framework_MockObject_MockObject
         */
        $closure = function ($class) {
            switch ($class) {
                case \Magento\Framework\Search\DocumentField::class:
                    return $this->documentField;
                case \Magento\Framework\Search\Document::class:
                    return $this->document;
            }
            return null;
        };

        $documents = [
            '_id' => 2,
            '_score' => 1.00,
            '_index' => 'indexName',
            '_type' => 'product',
        ];

        $this->entityMetadata->expects($this->once())
            ->method('getEntityId')
            ->willReturn('_id');

        $this->objectManager->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnCallback($closure));

        $result = $this->model->create($documents);
        $this->assertInstanceOf($this->instanceName, $result);
    }
}
