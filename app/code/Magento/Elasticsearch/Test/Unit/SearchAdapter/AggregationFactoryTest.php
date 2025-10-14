<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\AggregationFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Response\Aggregation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregationFactoryTest extends TestCase
{

    /**
     * @var AggregationFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->willReturn($this->createMock(Aggregation::class));
        $this->model = $objectManagerHelper->getObject(
            AggregationFactory::class,
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
