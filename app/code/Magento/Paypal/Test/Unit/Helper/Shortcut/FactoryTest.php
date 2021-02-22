<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Helper\Shortcut;

use Magento\Paypal\Helper\Shortcut\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Paypal\Helper\Shortcut\Factory */
    protected $factory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->factory = $this->objectManagerHelper->getObject(
            \Magento\Paypal\Helper\Shortcut\Factory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreateDefault()
    {
        $instance = $this->getMockBuilder(\Magento\Paypal\Helper\Shortcut\ValidatorInterface::class)->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')->with(Factory::DEFAULT_VALIDATOR)
            ->willReturn($instance);

        $this->assertInstanceOf(
            \Magento\Paypal\Helper\Shortcut\ValidatorInterface::class,
            $this->factory->create()
        );
    }

    public function testCreateCheckout()
    {
        $checkoutMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $instance = $this->getMockBuilder(\Magento\Paypal\Helper\Shortcut\ValidatorInterface::class)->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')->with(Factory::CHECKOUT_VALIDATOR)
            ->willReturn($instance);

        $this->assertInstanceOf(
            \Magento\Paypal\Helper\Shortcut\ValidatorInterface::class,
            $this->factory->create($checkoutMock)
        );
    }
}
