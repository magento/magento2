<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Block\Customer\Account;

use Magento\Customer\Block\Account\SortLink;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Store\Model\ScopeInterface;

/**
 *  BillingAgreementsLink
 */
class BillingAgreementsLink extends SortLink implements BillingAgreementsLinkInterface
{
    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!$this->canShowLink()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    protected function canShowLink(): bool
    {
        $websiteCode = $this->_storeManager->getWebsite()->getCode();

        $moduleEnabledDefault = $this->_scopeConfig->getValue(static::XML_PATH_PAYPAL_PAYMENT_ENABLED);
        $moduleEnabledWebsite = $this->_scopeConfig->getValue(
            static::XML_PATH_PAYPAL_PAYMENT_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteCode
        );

        $baSignupTypeDefault = $this->_scopeConfig->getValue(static::XML_PATH_BILLING_AGREEMENT_SIGNUP);
        $baSignupTypeWebsite = $this->_scopeConfig->getValue(
            static::XML_PATH_BILLING_AGREEMENT_SIGNUP,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteCode
        );

        $moduleEnabled = $moduleEnabledDefault || $moduleEnabledWebsite;
        $baSignupEnabled = $baSignupTypeDefault !== PaypalConfig::EC_BA_SIGNUP_NEVER ||
            $baSignupTypeWebsite !== PaypalConfig::EC_BA_SIGNUP_NEVER;

        return $moduleEnabled || $baSignupEnabled;
    }
}
