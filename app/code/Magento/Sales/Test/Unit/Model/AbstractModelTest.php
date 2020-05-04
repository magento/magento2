<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

class AbstractModelTest extends TestCase
{
    /**
     * @var Order
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Order::class);
    }

    public function testGetEventPrefix()
    {
        $this->assertEquals('sales_order', $this->model->getEventPrefix());
    }

    public function testGetEventObject()
    {
        $this->assertEquals('order', $this->model->getEventObject());
    }
}
