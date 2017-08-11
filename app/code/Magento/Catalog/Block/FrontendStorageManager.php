<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block;

use Magento\Catalog\Model\FrontendStorageConfigurationPool;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Framework\App\Config;
use Magento\Framework\View\Element\Template\Context;

/**
 * Provide information to frontend storage manager
 *
 * @api
 * @since 101.1.0
 */
class FrontendStorageManager extends \Magento\Framework\View\Element\Template
{
    /**
     * @var FrontendStorageConfigurationPool
     */
    private $storageConfigurationPool;

    /**
     * @var Config
     */
    private $appConfig;

    /**
     * @param Context $context
     * @param FrontendStorageConfigurationPool $storageConfigurationPool
     * @param Config $appConfig
     * @param array $data
     * @since 101.1.0
     */
    public function __construct(
        Context $context,
        FrontendStorageConfigurationPool $storageConfigurationPool,
        Config $appConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storageConfigurationPool = $storageConfigurationPool;
        $this->appConfig = $appConfig;
    }

    /**
     * Merge and retrieve configuration of storages like ids_storage or product_storage
     * in json format
     *
     * @return string
     * @since 101.1.0
     */
    public function getConfigurationJson()
    {
        $configuration = $this->getData('configuration') ?: [];

        foreach ($configuration as $namespace => & $storageConfig) {
            $dynamicStorage = $this->storageConfigurationPool->get($namespace);

            if ($dynamicStorage) {
                $storageConfig = array_replace_recursive($storageConfig, $dynamicStorage->get());
            }

            $storageConfig['allowToSendRequest'] = $this->appConfig->getValue(
                Synchronizer::ALLOW_SYNC_WITH_BACKEND_PATH
            );
        }

        return json_encode($configuration);
    }
}
