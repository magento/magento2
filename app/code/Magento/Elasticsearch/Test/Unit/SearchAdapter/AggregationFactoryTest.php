<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\AggregationFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AggregationFactoryTest
 */
class AggregationFactoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var AggregationFactory
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->willReturn($this->createMock(\Magento\Framework\Search\Response\Aggregation::class));
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\AggregationFactory::class,
            [
                'objectManager' => $this->objectManager
            ]
        );
    }

    /**
     * Test create() method.
     *
     * @return void
     */
    public function testCreate()
    {
        $object = $this->model->create(
            [
                'price_bucket' => [
                    'name' => 1,
                ],
                'category_bucket' => [],
            ]
        );
        $this->assertNotNull($object);
    }
}
