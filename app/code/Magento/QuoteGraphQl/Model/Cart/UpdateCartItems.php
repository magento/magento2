<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;

class UpdateCartItems
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        GuestCartRepositoryInterface $guestCartRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->guestCartRepository = $guestCartRepository;
    }

    public function update(string $maskedCartId, array $items): Quote
    {
        $quote = $this->guestCartRepository->get($maskedCartId);

        foreach ($items as $item) {
            $quoteItem = $quote->getItemById($item['item_id']);
            if ($quoteItem === false) {
                throw new NoSuchEntityException(__('Could not find cart item with id: %1', $item['item_id']));
            }

            $qty = $item['qty'];

            if ($qty <= 0.0) {
                $quote->removeItem($quoteItem->getItemId());
                continue;
            }

            $quoteItem->setQty($qty);
        }

        $this->quoteRepository->save($quote);

        return $quote;
    }
}
