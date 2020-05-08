<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\RequestInfoFilter;
use Magento\Checkout\Model\Cart\RequestInfoFilterComposite;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class RequestInfoFilterCompositeTest extends TestCase
{
    /**
     * @var RequestInfoFilterComposite
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $requestInfoFilterMock1 = $this->createPartialMock(
            RequestInfoFilter::class,
            ['filter']
        );
        $requestInfoFilterMock2 = $this->createPartialMock(
            RequestInfoFilter::class,
            ['filter']
        );

        $requestInfoFilterMock1->expects($this->atLeastOnce())
            ->method('filter');
        $requestInfoFilterMock2->expects($this->atLeastOnce())
            ->method('filter');

        $filterList = [ $requestInfoFilterMock1, $requestInfoFilterMock2];

        $this->model = $this->objectManager->getObject(
            RequestInfoFilterComposite::class,
            [
                'filters' => $filterList,
            ]
        );
    }

    /**
     * Test Filter method
     */
    public function testFilter()
    {
        /** @var DataObject $params */
        $params = $this->objectManager->getObject(
            DataObject::class,
            ['data' => ['abc' => 1, 'efg' => 1, 'xyz' => 1]]
        );
        $result = $this->model->filter($params);
        $this->assertEquals($this->model, $result);
    }
}
