<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\App;

use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class ApiMutableScopeConfig implements MutableScopeConfigInterface
{
    /** @var Config */
    private $testAppConfig;

    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var ConfigFactory */
    private $configFactory;

    /**
     * @param ScopeConfigInterface $config
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreRepositoryInterface $storeRepository,
        WebsiteRepositoryInterface $websiteRepository,
        ConfigFactory $configFactory
    ) {
        $this->testAppConfig = $config;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->configFactory = $configFactory;
    }

    /**
     * @inheritdoc
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->testAppConfig->isSetFlag($path, $scopeType, $scopeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->testAppConfig->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * @inheritdoc
     */
    public function setValue(
        $path,
        $value,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->persistConfig($path, $value, $scopeType, $scopeCode);
        return $this->testAppConfig->setValue($path, $value, $scopeType, $scopeCode);
    }

    /**
     * Clean app config cache
     *
     * @return void
     */
    public function clean()
    {
        $this->testAppConfig->clean();
    }

    /**
     * Persist config in database
     *
     * @param string $path
     * @param string $value
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    private function persistConfig(string $path, string $value, string $scopeType, ?string $scopeCode): void
    {
        $pathParts = explode('/', $path);
        $store = 0;
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
        if ($scopeType === ScopeInterface::SCOPE_STORE && $scopeCode !== null) {
            $store = $this->storeRepository->get($scopeCode)->getId();
            $configData['store'] = $store;
        } elseif ($scopeType === ScopeInterface::SCOPE_WEBSITES && $scopeCode !== null) {
            $website = $this->websiteRepository->get($scopeCode)->getId();
            $configData['store'] = '';
            $configData['website'] = $website;
        }

        $this->configFactory->create(['data' => $configData])->save();
    }
}
