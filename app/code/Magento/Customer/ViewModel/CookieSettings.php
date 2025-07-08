<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CookieSettings implements ArgumentInterface
{
    public const XML_PATH_COOKIE_DOMAIN = 'web/cookie/cookie_domain';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get cookie domain for a store view
     *
     * @return mixed
     */
    public function getCookieDomain()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_DOMAIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
