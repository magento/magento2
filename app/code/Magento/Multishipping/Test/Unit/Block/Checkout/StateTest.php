<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Multishipping\Block\Checkout\State;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var State
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $mShippingStateMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->mShippingStateMock =
            $this->createMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping\State::class);
        $this->model = $objectManager->getObject(
            \Magento\Multishipping\Block\Checkout\State::class,
            [
                'multishippingState' => $this->mShippingStateMock,
            ]
        );
    }

    public function testGetSteps()
    {
        $this->mShippingStateMock->expects($this->once())
            ->method('getSteps')->willReturn(['expected array']);

        $this->assertEquals(['expected array'], $this->model->getSteps());
    }
}
