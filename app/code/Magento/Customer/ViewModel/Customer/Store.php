<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Customer's store view model
 */
class Store implements OptionSourceInterface
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Store constructor.
     *
     * @param SystemStore $systemStore
     * @param ConfigShare $configShare
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        SystemStore $systemStore,
        ConfigShare $configShare,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor
    ) {
        $this->systemStore = $systemStore;
        $this->configShare = $configShare;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return $this->getStoreOptions();
    }

    /**
     * Adding website ID to options list
     *
     * @return array
     */
    private function getStoreOptions(): array
    {
        $options = $this->systemStore->getStoreValuesForForm();

        $websiteKey = null;
        foreach ($options as $key => $option) {
            if ($websiteKey === null) {
                $websiteKey = $key;
            }
            if (is_array($option['value']) && !empty($option['value'])) {
                $websiteId = null;
                foreach ($option['value'] as $storeViewKey => $storeView) {
                    $websiteId = $this->systemStore->getStoreData($storeView['value'])->getWebsiteId();
                    $options[$key]['value'][$storeViewKey]['website_id'] = $websiteId;
                }
                if ($websiteId) {
                    $options[$key]['website_id'] = $websiteId;
                    if ($websiteKey !== null) {
                        $options[$websiteKey]['website_id'] = $websiteId;
                        $websiteKey = null;
                    }
                }
            }
        }

        return $options;
    }
}
