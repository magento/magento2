<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
