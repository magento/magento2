<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;

/**
 * Update cart id from request param
 */
class UpdateCartId
{
    /**
     * @var RestRequest $request
     */
    private $request;

    /**
     * @param RestRequest $request
     */
    public function __construct(RestRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Update id from request if param cartId exist
     *
     * @param GuestCartItemRepositoryInterface $guestCartItemRepository
     * @param CartItemInterface $cartItem
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        GuestCartItemRepositoryInterface $guestCartItemRepository,
        CartItemInterface $cartItem
    ): void {
        $cartId = $this->request->getParam('cartId');

        if ($cartId) {
            $cartItem->setQuoteId($cartId);
        }
    }
}
