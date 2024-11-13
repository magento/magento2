<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Cart\Controller;

use Magento\Checkout\Controller\Cart;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\Cart\MultishippingClearItemAddress;

/**
 * Cleans shipping addresses and item assignments after MultiShipping flow
 */
class CartPlugin
{
    /**
     * @var MultishippingClearItemAddress
     */
    private $multishippingClearItemAddress;

    /**
     * @param MultishippingClearItemAddress $multishippingClearItemAddress
     */
    public function __construct(
        MultishippingClearItemAddress $multishippingClearItemAddress
    ) {
        $this->multishippingClearItemAddress = $multishippingClearItemAddress;
    }

    /**
     * Cleans shipping addresses and item assignments after MultiShipping flow
     *
     * @param Cart $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function beforeDispatch(Cart $subject, RequestInterface $request)
    {
        $this->multishippingClearItemAddress->clearAddressItem($subject, $request);
    }
}
