<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Inline;

use Magento\Developer\Helper\Data as DeveloperHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Inline Translation config
 */
class Config implements ConfigInterface
{
    /**
     * @var DeveloperHelper
     */
    protected $devHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DeveloperHelper $helper
     */
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig,
        DeveloperHelper $helper
    ) {
        $this->devHelper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function isActive($scope = null)
    {
        return $this->scopeConfig->isSetFlag(
            'dev/translate_inline/active',
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function isDevAllowed($scope = null)
    {
        return $this->devHelper->isDevAllowed($scope);
    }
}
