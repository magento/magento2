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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Category Flat Indexer Model
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Category\Indexer;

class Flat extends \Magento\Index\Model\Indexer\AbstractIndexer
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalog_category_flat_match_result';

    /**
     * Matched entity events
     *
     * @var array
     */
    protected $_matchedEntities = array(
        \Magento\Catalog\Model\Category::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
        \Magento\Core\Model\Store::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_DELETE
        ),
        \Magento\Core\Model\Store\Group::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
    );

    /**
     * Whether the indexer should be displayed on process/list page
     *
     * @return bool
     */
    /**
     * Catalog category flat
     *
     * @var \Magento\Catalog\Helper\Category\Flat
     */
    protected $_catalogCategoryFlat = null;

    /**
     * Catalog resource category flat
     *
     * @var \Magento\Catalog\Model\Resource\Category\Flat
     */
    protected $_resourceCategoryFlat;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Resource\Category\Flat $resourceCategoryFlat
     * @param \Magento\Catalog\Helper\Category\Flat $catalogCategoryFlat
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Category\Flat $resourceCategoryFlat,
        \Magento\Catalog\Helper\Category\Flat $catalogCategoryFlat,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_resourceCategoryFlat = $resourceCategoryFlat;
        $this->_catalogCategoryFlat = $catalogCategoryFlat;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function isVisible()
    {
        return $this->_catalogCategoryFlat->isEnabled() || !$this->_catalogCategoryFlat->isBuilt();
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Category Flat Data');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Reorganize EAV category structure to flat structure');
    }

    /**
     * Retrieve Catalog Category Flat Indexer model
     *
     * @return \Magento\Catalog\Model\Resource\Category\Flat
     */
    protected function _getIndexer()
    {
        return $this->_resourceCategoryFlat;
    }

    /**
     * Check if event can be matched by process
     * Overwrote for check is flat catalog category is enabled and specific save
     * category, store, store_group
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    public function matchEvent(\Magento\Index\Model\Event $event)
    {
        if (!$this->_catalogCategoryFlat->isAvailable() || !$this->_catalogCategoryFlat->isBuilt()) {
            return false;
        }

        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == \Magento\Core\Model\Store::ENTITY) {
            if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
                $result = true;
            } elseif ($event->getType() == \Magento\Index\Model\Event::TYPE_SAVE) {
                /** @var $store \Magento\Core\Model\Store */
                $store = $event->getDataObject();
                if ($store && ($store->isObjectNew()
                    || $store->dataHasChangedFor('group_id')
                    || $store->dataHasChangedFor('root_category_id')
                )) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        } elseif ($entity == \Magento\Core\Model\Store\Group::ENTITY) {
            /** @var $storeGroup \Magento\Core\Model\Store\Group */
            $storeGroup = $event->getDataObject();
            if ($storeGroup
                && ($storeGroup->dataHasChangedFor('website_id') || $storeGroup->dataHasChangedFor('root_category_id'))
            ) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = parent::matchEvent($event);
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _registerEvent(\Magento\Index\Model\Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case \Magento\Catalog\Model\Category::ENTITY:
                $this->_registerCatalogCategoryEvent($event);
                break;

            case \Magento\Core\Model\Store::ENTITY:
                if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
                    $this->_registerCoreStoreEvent($event);
                    break;
                }
            case \Magento\Core\Model\Store\Group::ENTITY:
                $event->addNewData('catalog_category_flat_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
                break;
        }
    }

    /**
     * Register data required by catalog category process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Category\Indexer\Flat
     */
    protected function _registerCatalogCategoryEvent(\Magento\Index\Model\Event $event)
    {
        switch ($event->getType()) {
            case \Magento\Index\Model\Event::TYPE_SAVE:
                /* @var $category \Magento\Catalog\Model\Category */
                $category = $event->getDataObject();

                /**
                 * Check if category has another affected category ids (category move result)
                 */
                $affectedCategoryIds = $category->getAffectedCategoryIds();
                if ($affectedCategoryIds) {
                    $event->addNewData('catalog_category_flat_affected_category_ids', $affectedCategoryIds);
                } else {
                    $event->addNewData('catalog_category_flat_category_id', $category->getId());
                }

                break;
        }
        return $this;
    }

    /**
     * Register core store delete process
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Category\Indexer\Flat
     */
    protected function _registerCoreStoreEvent(\Magento\Index\Model\Event $event)
    {
        if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
            /* @var $store \Magento\Core\Model\Store */
            $store = $event->getDataObject();
            $event->addNewData('catalog_category_flat_delete_store_id', $store->getId());
        }
        return $this;
    }

    /**
     * Process event
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _processEvent(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();

        if (!empty($data['catalog_category_flat_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['catalog_category_flat_category_id'])) {
            // catalog_product save
            $categoryId = $data['catalog_category_flat_category_id'];
            $this->_getIndexer()->synchronize($categoryId);
        } else if (!empty($data['catalog_category_flat_affected_category_ids'])) {
            $categoryIds = $data['catalog_category_flat_affected_category_ids'];
            $this->_getIndexer()->move($categoryIds);
        } else if (!empty($data['catalog_category_flat_delete_store_id'])) {
            $storeId = $data['catalog_category_flat_delete_store_id'];
            $this->_getIndexer()->deleteStores($storeId);
        }
    }

    /**
     * Rebuild all index data
     *
     */
    public function reindexAll()
    {
        $this->_getIndexer()->reindexAll();
    }
}
