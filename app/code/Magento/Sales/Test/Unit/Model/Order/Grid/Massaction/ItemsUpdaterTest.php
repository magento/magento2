<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Grid\Massaction;

use Magento\Framework\Authorization;
use Magento\Sales\Model\Order\Grid\Massaction\ItemsUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemsUpdaterTest extends TestCase
{
    /**
     * @var  ItemsUpdater
     */
    protected $itemUpdater;

    /**
     * @var Authorization|MockObject
     */
    protected $authorizationMock;

    protected function setUp(): void
    {
        $this->authorizationMock = $this->createMock(Authorization::class);
        $this->itemUpdater = new ItemsUpdater(
            $this->authorizationMock
        );
    }

    public function testUpdate()
    {
        $arguments =[
            'cancel_order' => null,
            'hold_order' => null,
            'unhold_order' => null,
            'other' => null
        ];
        $this->authorizationMock->expects($this->exactly(3))
            ->method('isAllowed')
            ->willReturnMap(
                [
                    ['Magento_Sales::cancel', null, false],
                    ['Magento_Sales::hold', null, false],
                    ['Magento_Sales::unhold', null, false],

                ]
            );
        $this->assertEquals(['other' => null], $this->itemUpdater->update($arguments));
    }
}
