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
 * Category products indexer model.
 * Responsibility for system actions:
 *  - Product save (changed assigned categories list)
 *  - Category save (changed assigned products list or category move)
 *  - Store save (new store creation, changed store group) - require reindex all data
 *  - Store group save (changed root category or group website) - require reindex all data
 *
 * @method \Magento\Catalog\Model\Resource\Category\Indexer\Product _getResource()
 * @method \Magento\Catalog\Model\Resource\Category\Indexer\Product getResource()
 * @method int getCategoryId()
 * @method \Magento\Catalog\Model\Category\Indexer\Product setCategoryId(int $value)
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Category\Indexer\Product setProductId(int $value)
 * @method int getPosition()
 * @method \Magento\Catalog\Model\Category\Indexer\Product setPosition(int $value)
 * @method int getIsParent()
 * @method \Magento\Catalog\Model\Category\Indexer\Product setIsParent(int $value)
 * @method int getStoreId()
 * @method \Magento\Catalog\Model\Category\Indexer\Product setStoreId(int $value)
 * @method int getVisibility()
 * @method \Magento\Catalog\Model\Category\Indexer\Product setVisibility(int $value)
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Category\Indexer;

class Product extends \Magento\Index\Model\Indexer\AbstractIndexer
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalog_category_product_match_result';

    /**
     * @var array
     */
    protected $_matchedEntities = array(
        \Magento\Catalog\Model\Product::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_MASS_ACTION
        ),
        \Magento\Catalog\Model\Category::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
        \Magento\Core\Model\Store::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
        \Magento\Core\Model\Store\Group::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
    );

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Category\Indexer\Product');
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Category Products');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Indexed category/products association');
    }

    /**
     * Check if event can be matched by process.
     * Overwrote for specific config save, store and store groups save matching
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    public function matchEvent(\Magento\Index\Model\Event $event)
    {
        $data      = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == \Magento\Core\Model\Store::ENTITY) {
            $store = $event->getDataObject();
            if ($store && ($store->isObjectNew() || $store->dataHasChangedFor('group_id'))) {
                $result = true;
            } else {
                $result = false;
            }
        } elseif ($entity == \Magento\Core\Model\Store\Group::ENTITY) {
            $storeGroup = $event->getDataObject();
            $hasDataChanges = $storeGroup && ($storeGroup->dataHasChangedFor('root_category_id')
                || $storeGroup->dataHasChangedFor('website_id'));
            if ($storeGroup && !$storeGroup->isObjectNew() && $hasDataChanges) {
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
     * Check if category ids was changed
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _registerEvent(\Magento\Index\Model\Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();
        switch ($entity) {
            case \Magento\Catalog\Model\Product::ENTITY:
               $this->_registerProductEvent($event);
                break;

            case \Magento\Catalog\Model\Category::ENTITY:
                $this->_registerCategoryEvent($event);
                break;

            case \Magento\Core\Model\Store::ENTITY:
            case \Magento\Core\Model\Store\Group::ENTITY:
                $process = $event->getProcess();
                $process->changeStatus(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
                break;
        }
        return $this;
    }

    /**
     * Register event data during product save process
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _registerProductEvent(\Magento\Index\Model\Event $event)
    {
        $eventType = $event->getType();
        if ($eventType == \Magento\Index\Model\Event::TYPE_SAVE) {
            $product = $event->getDataObject();
            /**
             * Check if product categories data was changed
             */
            if ($product->getIsChangedCategories() || $product->dataHasChangedFor('status')
                || $product->dataHasChangedFor('visibility') || $product->getIsChangedWebsites()) {
                $event->addNewData('category_ids', $product->getCategoryIds());
            }
        } else if ($eventType == \Magento\Index\Model\Event::TYPE_MASS_ACTION) {
            /* @var $actionObject \Magento\Object */
            $actionObject = $event->getDataObject();
            $attributes   = array('status', 'visibility');
            $rebuildIndex = false;

            // check if attributes changed
            $attrData = $actionObject->getAttributesData();
            if (is_array($attrData)) {
                foreach ($attributes as $attributeCode) {
                    if (array_key_exists($attributeCode, $attrData)) {
                        $rebuildIndex = true;
                        break;
                    }
                }
            }

            // check changed websites
            if ($actionObject->getWebsiteIds()) {
                $rebuildIndex = true;
            }

            // register affected products
            if ($rebuildIndex) {
                $event->addNewData('product_ids', $actionObject->getProductIds());
            }
        }
    }

    /**
     * Register event data during category save process
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _registerCategoryEvent(\Magento\Index\Model\Event $event)
    {
        $category = $event->getDataObject();
        /**
         * Check if product categories data was changed
         */
        if ($category->getIsChangedProductList()) {
            $event->addNewData('products_was_changed', true);
        }
        /**
         * Check if category has another affected category ids (category move result)
         */
        if ($category->getAffectedCategoryIds()) {
            $event->addNewData('affected_category_ids', $category->getAffectedCategoryIds());
        }
    }

    /**
     * Process event data and save to index
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _processEvent(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['catalog_category_product_reindex_all'])) {
            $this->reindexAll();
        }
        if (empty($data['catalog_category_product_skip_call_event_handler'])) {
            $this->callEventHandler($event);
        }
    }
}
