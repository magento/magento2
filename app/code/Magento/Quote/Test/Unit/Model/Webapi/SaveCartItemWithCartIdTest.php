<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Webapi;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Webapi\SaveCartItemWithCartId;

/**
 * Test for \Magento\Quote\Model\Webapi\SaveCartItemWithCartId
 */
class SaveCartItemWithCartIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var SaveCartItemWithCartId
     */
    private $model;

    protected function setUp()
    {
        $this->cartItemRepository = $this->getMockBuilder(CartItemRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = (new ObjectManager($this))->getObject(
            SaveCartItemWithCartId::class,
            [
                'cartItemRepository' => $this->cartItemRepository
            ]
        );
    }

    public function testQuoteIdIsCorrectlySet()
    {
        $cartId = 44;

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
        $cartId = 44;

        /** @var CartItemInterface $cartItem */
        $cartItem = $this->getMockBuilder(CartItemInterface::class)->getMockForAbstractClass();
        $cartItem->expects($this->once())
            ->method('setQuoteId')
            ->with($cartId)
            ->willReturnSelf();

        $this->cartItemRepository->expects($this->once())
            ->method('save')
            ->with($cartItem)
            ->willReturn($cartItem);

        $this->model->saveForCart($cartId, $cartItem);
    }
}
