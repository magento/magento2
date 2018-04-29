<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Webapi;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Webapi\SaveGuestCartItemWithCartId;

/**
 * Test for \Magento\Quote\Model\Webapi\SaveGuestCartItemWithCartId
 */
class SaveGuestCartItemWithCartIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestCartItemRepositoryInterface
     */
    private $guestCartItemRepo;

    /**
     * @var SaveGuestCartItemWithCartId
     */
    private $model;

    protected function setUp()
    {
        $this->guestCartItemRepo = $this->getMockBuilder(GuestCartItemRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = (new ObjectManager($this))->getObject(
            SaveGuestCartItemWithCartId::class,
            [
                'guestCartItemRepo' => $this->guestCartItemRepo
            ]
        );
    }

    public function testQuoteIdIsCorrectlySet()
    {
        $cartId = '298047a8923470823';

        /** @var CartItemInterface $cartItem */
        $cartItem = $this->getMockBuilder(CartItemInterface::class)->getMockForAbstractClass();
        $cartItem->expects($this->once())
            ->method('setQuoteId')
            ->with($cartId)
            ->willReturnSelf();

        $this->model->saveForCart($cartId, $cartItem);
    }

    public function testQuoteItemIsCorrectlySaved()
    {
        $cartId = '298047a8923470823';

        /** @var CartItemInterface $cartItem */
        $cartItem = $this->getMockBuilder(CartItemInterface::class)->getMockForAbstractClass();
        $cartItem->expects($this->once())
            ->method('setQuoteId')
            ->with($cartId)
            ->willReturnSelf();

        $this->guestCartItemRepo->expects($this->once())
            ->method('save')
            ->with($cartItem)
            ->willReturn($cartItem);

        $this->model->saveForCart($cartId, $cartItem);
    }
}
