<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\App;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\ObjectManager;

/**
 * @inheritdoc
 */
class MutableScopeConfig implements MutableScopeConfigInterface
{
    /**
     * @var Config
     */
    private $testAppConfig;

    /**
     * @inheritdoc
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->getTestAppConfig()->isSetFlag($path, $scopeType, $scopeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->getTestAppConfig()->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * @inheritdoc
     */
    public function setValue(
        $path,
        $value,
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->getTestAppConfig()->setValue($path, $value, $scopeType, $scopeCode);
    }

    /**
     * Clean app config cache
     *
     * @param string|null $type
     * @return void
     */
    public function clean()
    {
        $this->getTestAppConfig()->clean();
    }

    /**
     * Retrieve test app config instance
     *
     * @return \Magento\TestFramework\App\Config
     */
    private function getTestAppConfig()
    {
        if (!$this->testAppConfig) {
            $this->testAppConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        }

        return $this->testAppConfig;
    }
}
