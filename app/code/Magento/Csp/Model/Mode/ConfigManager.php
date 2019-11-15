<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Mode;

use Magento\Csp\Api\Data\ModeConfiguredInterface;
use Magento\Csp\Api\ModeConfigManagerInterface;
use Magento\Csp\Model\Mode\Data\ModeConfigured;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * @inheritDoc
 */
class ConfigManager implements ModeConfigManagerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var Store
     */
    private $storeModel;

    /**
     * @var State
     */
    private $state;

    /**
     * @param ScopeConfigInterface $config
     * @param Store $store
     * @param State $state
     */
    public function __construct(ScopeConfigInterface $config, Store $store, State $state)
    {
        $this->config = $config;
        $this->storeModel = $store;
        $this->state = $state;
    }

    /**
     * @inheritDoc
     */
    public function getConfigured(): ModeConfiguredInterface
    {
        $area = $this->state->getAreaCode();
        if ($area === Area::AREA_ADMINHTML) {
            $configArea = 'admin';
        } elseif ($area === Area::AREA_FRONTEND) {
            $configArea = 'storefront';
        } else {
            throw new \RuntimeException('CSP can only be configured for storefront or admin area');
        }

        $reportOnly = $this->config->isSetFlag(
            'csp/mode/' . $configArea .'/report_only',
            ScopeInterface::SCOPE_STORE,
            $this->storeModel->getStore()
        );
        $reportUri = $this->config->getValue(
            'csp/mode/' . $configArea .'/report_uri',
            ScopeInterface::SCOPE_STORE,
            $this->storeModel->getStore()
        );

        return new ModeConfigured($reportOnly, !empty($reportUri) ? $reportUri : null);
    }
}
