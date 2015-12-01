<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\AggregationFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AggregationFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AggregationFactory
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AggregationFactory(
            $this->objectManager
        );
    }

    /**
     * Test create() method.
     */
    public function testCreate()
    {
        $this->model->create(
            [
                'price_bucket' => [
                    'name' => 1,
                ],
                'category_bucket' => [],
            ]
        );
    }
}
