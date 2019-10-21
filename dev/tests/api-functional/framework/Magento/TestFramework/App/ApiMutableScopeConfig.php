<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\App;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\ObjectManager;

/**
 * @inheritdoc
 */
class ApiMutableScopeConfig implements MutableScopeConfigInterface
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
        $this->persistConfig($path, $value, $scopeType, $scopeCode);
        return $this->getTestAppConfig()->setValue($path, $value, $scopeType, $scopeCode);
    }

    /**
     * Clean app config cache
     *
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

    /**
     * Persist config in database
     *
     * @param string $path
     * @param string $value
     * @param string $scopeType
     * @param string|null $scopeCode
     */
    private function persistConfig($path, $value, $scopeType, $scopeCode): void
    {
        $pathParts = explode('/', $path);
        $store = 0;
        if ($scopeType === \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            && $scopeCode !== null) {
            $store = ObjectManager::getInstance()
                    ->get(\Magento\Store\Api\StoreRepositoryInterface::class)
                    ->get($scopeCode)
                    ->getId();
        }
        $configData = [
            'section' => $pathParts[0],
            'website' => '',
            'store' => $store,
            'groups' => [
                $pathParts[1] => [
                    'fields' => [
                        $pathParts[2] => [
                            'value' => $value
                        ]
                    ]
                ]
            ]
        ];
        ObjectManager::getInstance()
            ->get(\Magento\Config\Model\Config\Factory::class)
            ->create(['data' => $configData])
            ->save();
    }
}
