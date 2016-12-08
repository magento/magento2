<?php
/**
 * Default application path for backend area
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\TestFramework\Backend\App;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Backend config accessor.
 */
class Config extends \Magento\Backend\App\Config
{
    /**
     * @var \Magento\TestFramework\App\MutableScopeConfig
     */
    private $mutableScopeConfig;

    /**
     * Config constructor.
     * @param \Magento\TestFramework\App\Config $appConfig
     * @param \Magento\TestFramework\App\MutableScopeConfig $mutableScopeConfig
     */
    public function __construct(\Magento\TestFramework\App\Config $appConfig, \Magento\TestFramework\App\MutableScopeConfig $mutableScopeConfig)
    {
        parent::__construct(
            \Magento\TestFramework\ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopePool::class),
            $appConfig
        );
        $this->mutableScopeConfig = $mutableScopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function setValue(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->mutableScopeConfig->setValue($path, $value, $scope, $scopeCode);
    }
}
