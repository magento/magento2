<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Search engine provider
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * @api
 * @since 100.0.2
 */
class EngineProvider
{
    /**
     * @deprecated since using engine resolver
     * @see \Magento\Framework\Search\EngineResolverInterface
     */
    const CONFIG_ENGINE_PATH = 'catalog/search/engine';

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated since it is not used anymore
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Pool of existing engines
     *
     * @var array
     */
    private $enginePool;

    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $engines
     * @param EngineResolverInterface $engineResolver
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $engines,
        EngineResolverInterface $engineResolver
    ) {
        $this->objectManager = $objectManager;
        $this->enginePool = $engines;
        $this->engineResolver = $engineResolver;
    }

    /**
     * Get engine singleton
     *
     * @return EngineInterface
     */
    public function get()
    {
        if (!$this->engine) {
            $currentEngine = $this->engineResolver->getCurrentSearchEngine();
            if (!isset($this->enginePool[$currentEngine])) {
                throw new \LogicException(
                    'There is no such engine: ' . $currentEngine
                );
            }
            $engineClassName = $this->enginePool[$currentEngine];

            $engine = $this->objectManager->create($engineClassName);
            if (false === $engine instanceof EngineInterface) {
                throw new \LogicException(
                    $currentEngine . ' doesn\'t implement ' . EngineInterface::class
                );
            }

            /** @var $engine EngineInterface */
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
