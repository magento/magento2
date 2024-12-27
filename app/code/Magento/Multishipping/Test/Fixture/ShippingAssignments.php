<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class ShippingAssignments implements DataFixtureInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id'       => (int) Cart ID. Required.
     *      'assignments'   => [
     *          [
     *              'address_id' => (array) Cart Address ID. Required.
     *              'item_id'    => (int) Cart Item ID. Required.
     *              'qty'        => (int) Quantity. Optional. Default: 1.
     *          ]
     *      ]
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $cart = $this->cartRepository->get($data['cart_id']);
        foreach ($data['assignments'] as $assignment) {
            $cartAddress = $cart->getAddressById($assignment['address_id']);
            $cartItem = $cart->getItemById($assignment['item_id']);
            $qty = $assignment['qty'] ?? 1;
            $cartAddressItem = $cartAddress->getItemByQuoteItemId($assignment['item_id']);
            if ($cartAddressItem) {
                $cartAddressItem->setQty((int)($cartAddressItem->getQty() + $qty));
            } else {
                $cartAddress->addItem($cartItem, $qty);
            }
        }
        $cart->setTotalsCollectedFlag(false);
        $cart->collectTotals();
        $this->cartRepository->save($cart);
        return null;
    }
}
