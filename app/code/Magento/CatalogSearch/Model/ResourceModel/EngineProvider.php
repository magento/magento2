<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Search engine provider
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

use Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 2.0.0
 */
class EngineProvider
{
    const CONFIG_ENGINE_PATH = 'catalog/search/engine';

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\EngineInterface
     * @since 2.0.0
     */
    protected $engine;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * Pool of existing engines
     *
     * @var array
     * @since 2.0.0
     */
    private $enginePool;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $engines
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $engines
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->enginePool = $engines;
    }

    /**
     * Get engine singleton
     *
     * @return EngineInterface
     * @since 2.0.0
     */
    public function get()
    {
        if (!$this->engine) {
            $currentEngine = $this->scopeConfig->getValue(self::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);
            if (!isset($this->enginePool[$currentEngine])) {
                throw new \LogicException(
                    'There is no such engine: ' . $currentEngine
                );
            }
            $engineClassName = $this->enginePool[$currentEngine];

            $engine = $this->objectManager->create($engineClassName);
            if (false === $engine instanceof \Magento\CatalogSearch\Model\ResourceModel\EngineInterface) {
                throw new \LogicException(
                    $engineClassName . ' doesn\'t implement \Magento\CatalogSearch\Model\ResourceModel\EngineInterface'
                );
            }

            if ($engine && !$engine->isAvailable()) {
                throw new \LogicException(
                    'Engine is not available: ' . $currentEngine
                );
            }
            $this->engine = $engine;
        }

        return $this->engine;
    }
}
