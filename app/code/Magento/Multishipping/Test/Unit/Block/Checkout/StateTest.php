<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Multishipping\Block\Checkout\State;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var State
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mShippingStateMock;

    protected function setUp()
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
            ->method('getSteps')->will($this->returnValue(['expected array']));

        $this->assertEquals(['expected array'], $this->model->getSteps());
    }
}
