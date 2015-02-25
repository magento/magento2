<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Search engine provider
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\Store\Model\ScopeInterface;

class EngineProvider
{
    const CONFIG_ENGINE_PATH = 'catalog/search/engine';

    /**
     * @var \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    protected $_engine;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
    }

    /**
     * Get engine singleton
     *
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function get()
    {
        if (!$this->_engine) {
            $engineClassName = $this->_scopeConfig->getValue(self::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);

            /**
             * This needed if there already was saved in configuration some none-default engine
             * and module of that engine was disabled after that.
             * Problem is in this engine in database configuration still set.
             */
            if ($engineClassName) {
                $engine = $this->_objectManager->create($engineClassName);

                if (false === $engine instanceof \Magento\CatalogSearch\Model\Resource\EngineInterface) {
                    throw new \LogicException(
                        $engineClassName . ' doesn\'t implement \Magento\CatalogSearch\Model\Resource\EngineInterface'
                    );
                }
                if ($engine && $engine->test()) {
                    $this->_engine = $engine;
                }
            }
        }

        return $this->_engine;
    }
}
