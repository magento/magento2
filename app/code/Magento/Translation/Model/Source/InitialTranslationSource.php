<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\StoreManager;
use Magento\Translation\Model\ResourceModel\TranslateFactory;
use Magento\Translation\Model\ResourceModel\Translate;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;

/**
 * Class for reading translations from DB
 */
class InitialTranslationSource implements ConfigSourceInterface
{
    /**
     * @var TranslateFactory
     */
    private $translateFactory;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var array
     */
    private $data;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param TranslateFactory $translateFactory
     * @param StoreManager $storeManager
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        TranslateFactory $translateFactory,
        StoreManager $storeManager,
        DeploymentConfig $deploymentConfig
    ) {
        $this->translateFactory = $translateFactory;
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Read translations for the given 'path' from application initial configuration.
     *
     * @param string $path
     * @return mixed
     */
    public function get($path = '')
    {
        if (!$this->deploymentConfig->isDbAvailable()) {
            return [];
        }

        if (!$this->data) {
            /** @var Translate $translate */
            $translate = $this->translateFactory->create();
            $select = $translate->getConnection()->select()
                ->from($translate->getMainTable(), ['string', 'translate', 'store_id', 'locale'])
                ->order('store_id');
            $translations = [];
            foreach ($translate->getConnection()->fetchAll($select) as $item) {
                $store = $this->storeManager->getStore($item['store_id']);
                $translations[$item['locale']][$store->getCode()][$item['string']] = $item['translate'];
            }
            $this->data = new DataObject($translations);
        }
        return $this->data->getData($path) ?: [];
    }
}
