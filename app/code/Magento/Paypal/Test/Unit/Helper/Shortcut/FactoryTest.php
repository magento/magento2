<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Helper\Shortcut;

use Magento\Checkout\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Helper\Shortcut\Factory;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /** @var Factory */
    protected $factory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->factory = $this->objectManagerHelper->getObject(
            Factory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreateDefault()
    {
        $instance = $this->getMockBuilder(ValidatorInterface::class)
            ->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')->with(Factory::DEFAULT_VALIDATOR)
            ->willReturn($instance);

        $this->assertInstanceOf(
            ValidatorInterface::class,
            $this->factory->create()
        );
    }

    public function testCreateCheckout()
    {
        $checkoutMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $instance = $this->getMockBuilder(ValidatorInterface::class)
            ->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')->with(Factory::CHECKOUT_VALIDATOR)
            ->willReturn($instance);

        $this->assertInstanceOf(
            ValidatorInterface::class,
            $this->factory->create($checkoutMock)
        );
    }
}
