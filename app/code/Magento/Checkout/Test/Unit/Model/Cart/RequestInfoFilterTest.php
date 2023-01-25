<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\RequestInfoFilter;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class RequestInfoFilterTest extends TestCase
{
    /**
     * @var RequestInfoFilter
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

        $this->model = $this->objectManager->getObject(
            RequestInfoFilter::class,
            [
                'filterList' => ['efg', 'xyz'],
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
        $this->assertEquals(['abc' => 1], $params->convertToArray());
    }
}
