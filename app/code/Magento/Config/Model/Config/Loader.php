<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\ObjectManager;

/**
 * System configuration loader - Class which can read config by paths
 *
 * @api
 * @since 100.0.2
 */
class Loader
{
    /**
     * Config data factory
     *
     * @var \Magento\Framework\App\Config\ValueFactory
     * @deprecated
     * @see $collectionFactory
     */
    protected $_configValueFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param ?CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        ?CollectionFactory $collectionFactory = null
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->collectionFactory = $collectionFactory ?: ObjectManager::getInstance()->get(CollectionFactory::class);
    }

    /**
     * Get configuration value by path
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeId
     * @param bool $full
     * @return array
     */
    public function getConfigByPath($path, $scope, $scopeId, $full = true)
    {
        $configDataCollection = $this->collectionFactory->create();
        $configDataCollection->addScopeFilter($scope, $scopeId, $path);
        $config = [];
        $configDataCollection->load();
        foreach ($configDataCollection->getItems() as $data) {
            if ($full) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId(),
                ];
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }
}
