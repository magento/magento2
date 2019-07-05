<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CustomerGraphQl\Model\Plugin\Customer;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Plugin to update is_subscribed param according to system config
 */
class CreateCustomerAccount
{
    /**
     * Configuration path to newsletter active setting
     */
    const XML_PATH_NEWSLETTER_ACTIVE = 'newsletter/general/active';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CreateCustomerAccount constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Before Executing method.
     *
     * @param \Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount $subject
     * @param $data
     * @return array
     */
    public function beforeExecute (
        \Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount $subject, $data
    ) {
        if (!$this->scopeConfig->getValue(
            self::XML_PATH_NEWSLETTER_ACTIVE,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            $data['is_subscribed'] = false;
        }

        return [$data];
    }
}
