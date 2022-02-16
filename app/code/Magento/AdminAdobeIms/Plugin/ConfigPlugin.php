<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdobeIms\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigPlugin
{
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Config $subject
     * @param string $result
     * @return string
     */
    public function afterGetCallBackUrl(Config $subject, string $result): string
    {
        return $this->scopeConfig->getValue('web/secure/base_url');
    }
}
