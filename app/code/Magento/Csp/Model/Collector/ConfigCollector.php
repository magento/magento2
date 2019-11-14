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
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

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
     * @var Store
     */
    private $storeModel;

    /**
     * @param ScopeConfigInterface $config
     * @param PolicyReaderPool $readersPool
     * @param State $state
     * @param Store $storeModel
     */
    public function __construct(
        ScopeConfigInterface $config,
        PolicyReaderPool $readersPool,
        State $state,
        Store $storeModel
    ) {
        $this->config = $config;
        $this->readersPool = $readersPool;
        $this->state = $state;
        $this->storeModel = $storeModel;
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
            $policiesConfig = $this->config->getValue(
                'csp/policies/' . $configArea,
                ScopeInterface::SCOPE_STORE,
                $this->storeModel->getStore()
            );
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
