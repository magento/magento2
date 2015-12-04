<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Design\Config\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Theme\Model\Design\Config\MetadataProviderInterface;

/**
 * Data collection
 *
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var ScopeTreeProviderInterface
     */
    protected $scopeTree;

    /**
     * @var MetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var ScopeConfigInterface
     */
    protected $appConfig;

    /**
     * Collection constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param ScopeTreeProviderInterface $scopeTree
     * @param MetadataProviderInterface $metadataProvider
     * @param ScopeConfigInterface $appConfig
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        ScopeTreeProviderInterface $scopeTree,
        MetadataProviderInterface $metadataProvider,
        ScopeConfigInterface $appConfig
    ) {
        parent::__construct($entityFactory);
        $this->scopeTree = $scopeTree;
        $this->metadataProvider = $metadataProvider;
        $this->appConfig = $appConfig;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Theme\Model\ResourceModel\Design\Config\Scope\Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $default = $this->scopeTree->get();
            $this->prepareItemData();
            foreach ($default['scopes'] as $website) {
                $this->prepareItemData($website);
                foreach ($website['scopes'] as $group) {
                    foreach ($group['scopes'] as $store) {
                        $this->prepareItemData($website, $group, $store);
                    }
                }
            }
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * Retrieve fields metadata
     *
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    protected function getMetadataValues($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = null)
    {
        $result = [];
        foreach ($this->metadataProvider->get() as $itemName => $itemData) {
            if (isset($itemData['use_in_grid']) && (boolean)$itemData['use_in_grid']) {
                $result[$itemName] = $this->appConfig->getValue($itemData['path'], $scope, $scopeId);
            }
        }

        return $result;
    }

    /**
     * Prepare item data depend on scope
     *
     * @param array $websiteScope
     * @param array $groupScope
     * @param array $storeScope
     *
     * @return void
     */
    protected function prepareItemData(array $websiteScope = [], array $groupScope = [], array $storeScope = [])
    {
        $result = [
            'store_website_id' => isset($websiteScope['scope_id']) ? $websiteScope['scope_id'] : null,
            'store_group_id' => isset($groupScope['scope_id']) ? $groupScope['scope_id'] : null,
            'store_id' => isset($storeScope['scope_id']) ? $storeScope['scope_id'] : null,
        ];

        if (isset($storeScope['scope'])) {
            $data = $this->getMetadataValues($storeScope['scope'], $storeScope['scope_id']);
        } elseif (isset($groupScope['scope'])) {
            $data = $this->getMetadataValues($groupScope['scope'], $groupScope['scope_id']);
        } elseif (isset($websiteScope['scope'])) {
            $data = $this->getMetadataValues($websiteScope['scope'], $websiteScope['scope_id']);
        } else {
            $data = $this->getMetadataValues();
        }

        $result = array_merge($result, $data);

        $this->_addItem(new \Magento\Framework\DataObject($result));
    }
}
