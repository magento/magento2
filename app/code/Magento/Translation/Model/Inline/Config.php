<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Inline;

/**
 * Inline Translation config
 * @since 2.0.0
 */
class Config implements \Magento\Framework\Translate\Inline\ConfigInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Developer\Helper\Data
     * @since 2.0.0
     */
    protected $devHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Developer\Helper\Data $helper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Developer\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->devHelper = $helper;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isDevAllowed($scope = null)
    {
        return $this->devHelper->isDevAllowed($scope);
    }
}
