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
    protected $_scopeConfig;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Core\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Core\Helper\Data $helper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function isActive($scope = null)
    {
        return $this->_scopeConfig->isSetFlag(
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
        return $this->_helper->isDevAllowed($scope);
    }
}
