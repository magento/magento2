<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\ResourceModel\Page;

use Magento\Cms\Api\Data\PageInterface;
use \Magento\Cms\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'page_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     *
     * @var array|null
     */
    private $storeLabelsCache;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Cms\Model\Page::class, \Magento\Cms\Model\ResourceModel\Page::class);
        $this->_map['fields']['page_id'] = 'main_table.page_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Returns pairs identifier - title for unique identifiers
     * and pairs identifier|page_id - title for non-unique after first
     *
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];
        foreach ($this as $item) {
            $identifier = $item->getData('identifier');

            $data['value'] = $identifier;
            $data['label'] = $item->getData('title');
            $data['store_ids'] = $item->getData('store_id');

            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getData('page_id');
            } else {
                $existingIdentifiers[] = $identifier;
            }

            $res[] = $data;
        }

        // sort cms pages by their title
        usort($res, function ($optionA, $optionB) {
            return strcmp($optionA['label'], $optionB['label']);
        });

        $res = $this->groupOptionsByStoreLabels($res);

        return $res;
    }

    /**
     * Changes the options, so they are grouped by store labels
     *
     * @param array $options
     * @return array
     */
    private function groupOptionsByStoreLabels(array $options)
    {
        $grouped = [];

        foreach ($options as $option) {
            $storeIds = $option['store_ids'];
            foreach ($storeIds as $storeId) {
                if (!array_key_exists($storeId, $grouped)) {
                    $grouped[$storeId] = ['label' => $this->replaceStoreIdWithLabel($storeId), 'value' => []];
                }
                $grouped[$storeId]['value'][] = ['label' => $option['label'], 'value' => $option['value']];
            }
        }

        // sort by store labels
        usort($grouped, function ($storeA, $storeB) {
            return strcmp($storeA['label'], $storeB['label']);
        });

        return ['values' => ['label' => '', 'value' => $grouped]];
    }

    /**
     * Replace the store id we receive with the store label
     * If label isn't found, we return the store id back (this "should" never happen)
     *
     * @param string $storeId
     * @return string
     */
    private function replaceStoreIdWithLabel(string $storeId)
    {
        $storeLabels = $this->getStoreLabels();

        if (array_key_exists($storeId, $storeLabels)) {
            return $storeLabels[$storeId];
        }

        return $storeId;
    }

    /**
     * Get array with all store labels, in the form of 'WebsiteName > StoreName > StoreviewName'
     * The key of the array is the store id
     *
     * @return array
     */
    private function getStoreLabels()
    {
        if ($this->storeLabelsCache === null) {
            $this->storeLabelsCache = [];

            $websites = $this->storeManager->getWebsites();
            foreach ($websites as $website) {
                $groups = $website->getGroups();
                foreach ($groups as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $this->storeLabelsCache[$store->getStoreId()] =
                            "{$website->getName()} > {$group->getName()} > {$store->getName()}";
                    }
                }
            }

            // also add a label for the default/admin store id
            $this->storeLabelsCache[\Magento\Store\Model\Store::DEFAULT_STORE_ID] = __('All Store Views');
        }

        return $this->storeLabelsCache;
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $this->performAfterLoad('cms_page_store', $entityMetadata->getLinkField());
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $this->joinStoreRelationTable('cms_page_store', $entityMetadata->getLinkField());
    }
}
