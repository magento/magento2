<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class AccountInformation
{
    private const XML_PATH_SHARE_ALL_CUSTOMER_GROUPS = 'customer/account_information/graphql_share_all_customer_groups';
    private const XML_PATH_SHARE_CUSTOMER_GROUP = 'customer/account_information/graphql_share_customer_group';

    /**
     * AccountInformation constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Is 'graphql_share_all_customer_groups' config enabled
     *
     * @return bool
     */
    public function isShareAllCustomerGroupsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHARE_ALL_CUSTOMER_GROUPS);
    }

    /**
     * Is 'graphql_share_customer_group' config enabled
     *
     * @return bool
     */
    public function isShareCustomerGroupEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHARE_CUSTOMER_GROUP);
    }
}
