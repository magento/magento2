<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\AggregationFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AggregationFactoryTest
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
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
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
