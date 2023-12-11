<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Block\Checkout\State as StateBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var StateBlock
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $mShippingStateMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
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
