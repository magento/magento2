<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveTest extends TestCase
{
    /**
     * @var Remove
     */
    protected $model;

    /** @var Cart|MockObject */
    protected $cartHelperMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->cartHelperMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            Remove::class,
            [
                'cartHelper' => $this->cartHelperMock,
            ]
        );
    }

    public function testGetConfigureUrl()
    {
        $json = '{json;}';

        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartHelperMock->expects($this->once())
            ->method('getDeletePostJson')
            ->with($itemMock)
            ->willReturn($json);

        $this->model->setItem($itemMock);
        $this->assertEquals($json, $this->model->getDeletePostJson());
    }
}
