<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Inline;

/**
 * Inline Translation config
 */
class Config implements \Magento\Framework\Translate\Inline\ConfigInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\Helper\Data
     */
    protected $configHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configHelper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function isActive($scope = null)
    {
        return $this->scopeConfig->isSetFlag(
            'dev/translate_inline/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function isDevAllowed($scope = null)
    {
        return $this->configHelper->isDevAllowed($scope);
    }
}
