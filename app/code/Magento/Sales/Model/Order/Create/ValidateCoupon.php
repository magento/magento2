<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Create;

use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\Data\CartInterface;

class ValidateCoupon
{
    /**
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     */
    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly Escaper $escaper
    ) {
    }

    /**
     * Validate coupon applied to the quote
     *
     * @param CartInterface $quote
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(CartInterface $quote, $data): void
    {
        $couponCode = '';
        if (isset($data['coupon']['code'])) {
            $couponCode = trim($data['coupon']['code']);
        }

        if (empty($couponCode)) {
            if (isset($data['coupon']['code']) && $couponCode == '') {
                $this->messageManager->addSuccessMessage(__('The coupon code has been removed.'));
            }
            return;
        }

        $isApplyDiscount = false;
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getNoDiscount()) {
                $isApplyDiscount = true;
                break;
            }
        }
        if (!$isApplyDiscount) {
            $this->messageManager->addErrorMessage(
                __(
                    '"%1" coupon code was not applied. Do not apply discount is selected for item(s)',
                    $this->escaper->escapeHtml($couponCode)
                )
            );
        } else {
            if ($quote->getCouponCode() !== $couponCode) {
                $this->messageManager->addErrorMessage(
                    __(
                        'The "%1" coupon code isn\'t valid. Verify the code and try again.',
                        $this->escaper->escapeHtml($couponCode)
                    )
                );
            } else {
                $this->messageManager->addSuccessMessage(__('The coupon code has been accepted.'));
            }
        }
    }
}
