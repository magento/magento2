<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InstantPurchase\Test\Unit\Model\Ui;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\InstantPurchase\Model\Ui\ShippingMethodFormatter;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingMethodFormatterTest extends TestCase
{
    /**
     * @var ShippingMethodFormatter|MockObject
     */
    private $shippingMethodFormatter;

    /**
     * Setup environment for testing
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->shippingMethodFormatter = $objectManager->getObject(ShippingMethodFormatter::class);
    }

    /**
     * Test format()
     */
    public function testFormat()
    {
        $shippingMethodMock = $this->createMock(ShippingMethodInterface::class, ['getCarrierTitle', 'getMethodTitle']);

        $shippingMethodMock->expects($this->any())->method('getCarrierTitle')->willReturn('flatrate');
        $shippingMethodMock->expects($this->any())->method('getMethodTitle')->willReturn('flatrate');

        $this->assertEquals(
            'flatrate - flatrate',
            $this->shippingMethodFormatter->format($shippingMethodMock)
        );
    }
}
