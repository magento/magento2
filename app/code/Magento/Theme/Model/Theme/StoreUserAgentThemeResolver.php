<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;

/**
 * Store associated themes in user-agent rules resolver,
 */
class StoreUserAgentThemeResolver implements StoreThemesResolverInterface
{
    private const XML_PATH_THEME_USER_AGENT = 'design/theme/ua_regexp';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $serializer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getThemes(StoreInterface $store): array
    {
        $config = $this->scopeConfig->getValue(
            self::XML_PATH_THEME_USER_AGENT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $rules = $config ? $this->serializer->unserialize($config) : [];
        $themes = [];
        if ($rules) {
            $themes = array_values(array_unique(array_column($rules, 'value')));
        }
        return $themes;
    }
}
