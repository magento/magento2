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
namespace Magento\Catalog\Model\Indexer;

/**
 * Catalog url rewrites index model.
 * Responsibility for system actions:
 *  - Product save (changed assigned categories list, assigned websites or url key)
 *  - Category save (changed assigned products list, category move, changed url key)
 *  - Store save (new store creation, changed store group) - require reindex all data
 *  - Store group save (changed root category or group website) - require reindex all data
 *  - Seo config settings change - require reindex all data
 */
class Url extends \Magento\Index\Model\Indexer\AbstractIndexer
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalog_url_match_result';

    /**
     * Index math: product save, category save, store save
     * store group save, config save
     *
     * @var array
     */
    protected $_matchedEntities = array(
        \Magento\Catalog\Model\Product::ENTITY => array(\Magento\Index\Model\Event::TYPE_SAVE),
        \Magento\Catalog\Model\Category::ENTITY => array(\Magento\Index\Model\Event::TYPE_SAVE),
        \Magento\Store\Model\Store::ENTITY => array(\Magento\Index\Model\Event::TYPE_SAVE),
        \Magento\Store\Model\Group::ENTITY => array(\Magento\Index\Model\Event::TYPE_SAVE),
        \Magento\Framework\App\Config\ValueInterface::ENTITY => array(\Magento\Index\Model\Event::TYPE_SAVE)
    );

    /**
     * Related Config Settings
     *
     * @var array
     */
    protected $_relatedConfigSettings = array(
        \Magento\Catalog\Helper\Category::XML_PATH_CATEGORY_URL_SUFFIX,
        \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_SUFFIX,
        \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY
    );

    /**
     * Catalog url
     *
     * @var \Magento\Catalog\Model\Url
     */
    protected $_catalogUrl;

    /**
     * Catalog url1
     *
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $_catalogResourceUrl;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Resource\UrlFactory $catalogResourceUrlFactory
     * @param \Magento\Catalog\Model\Url $catalogUrl
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Resource\UrlFactory $catalogResourceUrlFactory,
        \Magento\Catalog\Model\Url $catalogUrl,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_catalogResourceUrl = $catalogResourceUrlFactory->create();
        $this->_catalogUrl = $catalogUrl;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Catalog URL Rewrites');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Index product and categories URL Redirects');
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
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == \Magento\Store\Model\Store::ENTITY) {
            $store = $event->getDataObject();
            if ($store && ($store->isObjectNew() || $store->dataHasChangedFor('group_id'))) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            if ($entity == \Magento\Store\Model\Group::ENTITY) {
                /** @var \Magento\Store\Model\Group $storeGroup */
                $storeGroup = $event->getDataObject();
                $hasDataChanges = $storeGroup && ($storeGroup->dataHasChangedFor(
                    'root_category_id'
                ) || $storeGroup->dataHasChangedFor(
                    'website_id'
                ));
                if ($storeGroup && !$storeGroup->isObjectNew() && $hasDataChanges) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                if ($entity == \Magento\Framework\App\Config\ValueInterface::ENTITY) {
                    $configData = $event->getDataObject();
                    if ($configData && in_array($configData->getPath(), $this->_relatedConfigSettings)) {
                        $result = $configData->isValueChanged();
                    } else {
                        $result = false;
                    }
                } else {
                    $result = parent::matchEvent($event);
                }
            }
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return $this
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

            case \Magento\Store\Model\Store::ENTITY:
            case \Magento\Store\Model\Store::ENTITY:
            case \Magento\Framework\App\Config\ValueInterface::ENTITY:
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
     * @return void
     */
    protected function _registerProductEvent(\Magento\Index\Model\Event $event)
    {
        $product = $event->getDataObject();
        $dataChange = $product->dataHasChangedFor(
            'url_key'
        ) || $product->getIsChangedCategories() || $product->getIsChangedWebsites();

        if (!$product->getExcludeUrlRewrite() && $dataChange) {
            $event->addNewData('rewrite_product_ids', array($product->getId()));
        }
    }

    /**
     * Register event data during category save process
     *
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _registerCategoryEvent(\Magento\Index\Model\Event $event)
    {
        $category = $event->getDataObject();
        if (!$category->getInitialSetupFlag() && $category->getLevel() > 1) {
            if ($category->dataHasChangedFor('url_key') || $category->getIsChangedProductList()) {
                $event->addNewData('rewrite_category_ids', array($category->getId()));
            }
            /**
             * Check if category has another affected category ids (category move result)
             */
            if ($category->getAffectedCategoryIds()) {
                $event->addNewData('rewrite_category_ids', $category->getAffectedCategoryIds());
            }
        }
    }

    /**
     * Process event
     *
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _processEvent(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['catalog_url_reindex_all'])) {
            $this->reindexAll();
        }

        // Force rewrites history saving
        $dataObject = $event->getDataObject();
        if ($dataObject instanceof \Magento\Framework\Object && $dataObject->hasData('save_rewrites_history')) {
            $this->_catalogUrl->setShouldSaveRewritesHistory($dataObject->getData('save_rewrites_history'));
        }

        if (isset($data['rewrite_product_ids'])) {
            $this->_catalogUrl->clearStoreInvalidRewrites();
            // Maybe some products were moved or removed from website
            foreach ($data['rewrite_product_ids'] as $productId) {
                $this->_catalogUrl->refreshProductRewrite($productId);
            }
        }
        if (isset($data['rewrite_category_ids'])) {
            $this->_catalogUrl->clearStoreInvalidRewrites();
            // Maybe some categories were moved
            foreach ($data['rewrite_category_ids'] as $categoryId) {
                $this->_catalogUrl->refreshCategoryRewrite($categoryId, null, true, true);
            }
        }
    }

    /**
     * Rebuild all index data
     *
     * @return void
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->_catalogResourceUrl->beginTransaction();
        try {
            $this->_catalogUrl->refreshRewrites();
            $this->_catalogResourceUrl->commit();
        } catch (\Exception $e) {
            $this->_catalogResourceUrl->rollBack();
            throw $e;
        }
    }
}
