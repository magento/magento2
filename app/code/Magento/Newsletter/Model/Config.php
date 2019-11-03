<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Newsletter configuration model
 */
class Config
{
    /**
     * Configuration path to newsletter active setting
     */
    private const XML_PATH_NEWSLETTER_ACTIVE = 'newsletter/general/active';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns newsletter's enabled status
     *
     * @param string $scopeType
     * @return bool
     */
    public function isActive(string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_NEWSLETTER_ACTIVE, $scopeType);
    }
}
