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
            $data = [
                'store_website_id' => null,
                'store_group_id' => null,
                'store_id' => null,
            ];
            $data = array_merge($data, $this->getMetadataValues($default['scope'], $default['scope_id']));
            $this->_addItem($data);
            foreach ($default['scopes'] as $website) {
                $data = [
                    'store_website_id' => $website['scope_id'],
                    'store_group_id' => null,
                    'store_id' => null,
                ];
                $data = array_merge($data, $this->getMetadataValues($website['scope'], $website['scope_id']));
                $this->_addItem($data);
                foreach ($website['scopes'] as $group) {
                    foreach ($group['scopes'] as $store) {
                        $data = [
                            'store_website_id' => $website['scope_id'],
                            'store_group_id' => $group['scope_id'],
                            'store_id' => $store['scope_id'],
                        ];
                        $data = array_merge($data, $this->getMetadataValues($store['scope'], $store['scope_id']));
                        $this->_addItem($data);
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
    protected function getMetadataValues($scope, $scopeId)
    {
        $result = [];
        foreach ($this->metadataProvider->get() as $itemName => $itemData) {
            if (isset($itemData['use_in_grid']) && (boolean)$itemData['use_in_grid']) {
                $result[$itemName] = $this->appConfig->getValue($itemData['path'], $scope, $scopeId);
            }
        }

        return $result;
    }
}
