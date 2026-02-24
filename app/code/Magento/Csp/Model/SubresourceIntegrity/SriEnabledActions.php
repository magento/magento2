<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

/**
 * Defines which payment page actions can add SRI attributes
 */
class SriEnabledActions
{
    /**
     * @var array $paymentActions
     */
    private array $paymentActions;

    /**
     * @param array $paymentActions
     */
    public function __construct(
        array $paymentActions = []
    ) {
        $this->paymentActions = $paymentActions;
    }

    /**
     * Check if action is for payment page on storefront or admin
     *
     * @param string $actionName
     * @return bool
     */
    public function isPaymentPageAction(string $actionName): bool
    {
        return in_array(
            $actionName,
            $this->paymentActions
        );
    }
}
