<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Session disable in graphql configuration model.
 */
class DisableSession
{
    private const XML_PATH_GRAPHQL_DISABLE_SESSION = 'graphql/session/disable';

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
     * Get config value is session disabled for grapqhl area.
     *
     * @param string $scopeType
     * @param null|int|string $scopeCode
     * @return bool
     */
    public function isDisabled($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_GRAPHQL_DISABLE_SESSION,
            $scopeType,
            $scopeCode
        );

        if ($value === '1') {
            return true;
        }

        return false;
    }
}
