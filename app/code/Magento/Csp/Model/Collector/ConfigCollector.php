<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Collector\Config\PolicyReaderPool;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Reads Magento config.
 */
class ConfigCollector implements PolicyCollectorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var PolicyReaderPool
     */
    private $readersPool;

    /**
     * @var State
     */
    private $state;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @param ScopeConfigInterface $config
     * @param PolicyReaderPool $readersPool
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param Http|null $request
     */
    public function __construct(
        ScopeConfigInterface $config,
        PolicyReaderPool $readersPool,
        State $state,
        StoreManagerInterface $storeManager,
        ?Http $request = null
    ) {
        $this->config = $config;
        $this->readersPool = $readersPool;
        $this->state = $state;
        $this->storeManager = $storeManager;

        $this->request = $request
            ?? ObjectManager::getInstance()->get(Http::class);
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        $collected = $defaultPolicies;

        $configArea = null;
        $area = $this->state->getAreaCode();
        if ($area === Area::AREA_ADMINHTML) {
            $configArea = 'admin';
        } elseif ($area === Area::AREA_FRONTEND) {
            $configArea = 'storefront';
        }

        if ($configArea) {
            $policiesConfigGlobal = $this->config->getValue(
                'csp/policies/' . $configArea,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );

            $policiesConfigLocal = $this->config->getValue(
                sprintf(
                    'csp/policies/%s_%s',
                    $configArea,
                    $this->request->getFullActionName()
                ),
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );

            $policiesConfig = is_array($policiesConfigLocal) ?
                array_replace_recursive($policiesConfigGlobal, $policiesConfigLocal) :
                $policiesConfigGlobal;

            if (is_array($policiesConfig) && $policiesConfig) {
                foreach ($policiesConfig as $policyConfig) {
                    $collected[] = $this->readersPool->getReader($policyConfig['policy_id'])
                        ->read($policyConfig['policy_id'], $policyConfig);
                }
            }
        }

        return $collected;
    }
}
