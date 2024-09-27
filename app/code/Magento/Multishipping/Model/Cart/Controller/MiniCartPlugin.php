<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Cart\Controller;

use Magento\Checkout\Controller\Sidebar\UpdateItemQty;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\Cart\MultishippingClearItemAddress;

/**
 * Cleans shipping addresses and item assignments after MultiShipping flow
 */
class MiniCartPlugin
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
     * @param HttpPostActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function beforeDispatch(HttpPostActionInterface $subject, RequestInterface $request)
    {
        $this->multishippingClearItemAddress->clearAddressItem($subject, $request);
    }
}
