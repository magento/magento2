<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Validator\MinimumOrderAmount;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Store\Model\ScopeInterface;

class ValidationMessage
{
    private const XML_PATH_MINIMUM_ORDER_DESCRIPTION = 'sales/minimum_order/description';
    private const XML_PATH_MINIMUM_ORDER_AMOUNT = 'sales/minimum_order/amount';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $priceHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Data $priceHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Get validation message.
     *
     * @return Phrase
     */
    public function getMessage()
    {
        $message = $this->scopeConfig->getValue(
            static::XML_PATH_MINIMUM_ORDER_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
        if (!$message) {
            $minimumAmount = $this->priceHelper->currency($this->scopeConfig->getValue(
                static::XML_PATH_MINIMUM_ORDER_AMOUNT,
                ScopeInterface::SCOPE_STORE
            ), true, false);

            $message = __('Minimum order amount is %1', $minimumAmount);
        } else {
            //Added in order to address the issue: https://github.com/magento/magento2/issues/8287
            $message = __($message);
        }

        return $message;
    }
}
