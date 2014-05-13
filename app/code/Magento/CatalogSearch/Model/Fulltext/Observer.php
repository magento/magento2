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

/**
 * CatalogSearch Fulltext Observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Model\Fulltext;

class Observer
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog search fulltext
     *
     * @var \Magento\CatalogSearch\Model\Fulltext
     */
    protected $_catalogSearchFulltext;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Backend url
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Construct
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CatalogSearch\Model\Fulltext $catalogSearchFulltext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_backendSession = $backendSession;
        $this->_backendUrl = $backendUrl;
        $this->_eavConfig = $eavConfig;
        $this->_catalogSearchFulltext = $catalogSearchFulltext;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * Retrieve fulltext (indexer) model
     *
     * @return \Magento\CatalogSearch\Model\Fulltext
     */
    protected function _getFulltextModel()
    {
        return $this->_catalogSearchFulltext;
    }

    /**
     * Update product index when product data updated
     *
     * @deprecated since 1.11
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function refreshProductIndex(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $this->_getFulltextModel()->rebuildIndex(null, $product->getId())->resetSearchResults();

        return $this;
    }

    /**
     * Clean product index when product deleted or marked as unsearchable/invisible
     *
     * @deprecated since 1.11
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function cleanProductIndex(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $this->_getFulltextModel()->cleanIndex(null, $product->getId())->resetSearchResults();

        return $this;
    }

    /**
     * Update all attribute-dependant index
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function eavAttributeChange(\Magento\Framework\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
        $entityType = $this->_eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY);
        /* @var $entityType \Magento\Eav\Model\Entity\Type */

        if ($attribute->getEntityTypeId() != $entityType->getId()) {
            return $this;
        }
        $delete = $observer->getEventName() == 'eav_entity_attribute_delete_after';

        if (!$delete && !$attribute->dataHasChangedFor('is_searchable')) {
            return $this;
        }

        $showNotice = false;
        if ($delete) {
            if ($attribute->getIsSearchable()) {
                $showNotice = true;
            }
        } elseif ($attribute->dataHasChangedFor('is_searchable')) {
            $showNotice = true;
        }

        if ($showNotice) {
            $url = $this->_backendUrl->getUrl('adminhtml/system_cache');
            $this->messageManager->addNotice(
                __(
                    'Attribute setting change related with Search Index. Please run <a href="%1">Rebuild Search Index</a> process.',
                    $url
                )
            );
        }

        return $this;
    }

    /**
     * Rebuild index after import
     *
     * @return \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function refreshIndexAfterImport()
    {
        $this->_getFulltextModel()->rebuildIndex();
        return $this;
    }

    /**
     * Refresh fulltext index when we add new store
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function refreshStoreIndex(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $observer->getEvent()->getStore()->getId();
        $this->_getFulltextModel()->rebuildIndex($storeId);
        return $this;
    }

    /**
     * Catalog Product mass website update
     *
     * @deprecated since 1.11
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function catalogProductWebsiteUpdate(\Magento\Framework\Event\Observer $observer)
    {
        $websiteIds = $observer->getEvent()->getWebsiteIds();
        $productIds = $observer->getEvent()->getProductIds();
        $actionType = $observer->getEvent()->getAction();

        foreach ($websiteIds as $websiteId) {
            foreach ($this->_storeManager->getWebsite($websiteId)->getStoreIds() as $storeId) {
                if ($actionType == 'remove') {
                    $this->_getFulltextModel()->cleanIndex($storeId, $productIds)->resetSearchResults();
                } elseif ($actionType == 'add') {
                    $this->_getFulltextModel()->rebuildIndex($storeId, $productIds)->resetSearchResults();
                }
            }
        }

        return $this;
    }

    /**
     * Store delete processing
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\CatalogSearch\Model\Fulltext\Observer
     */
    public function cleanStoreIndex(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        /* @var $store \Magento\Store\Model\Store */

        $this->_getFulltextModel()->cleanIndex($store->getId());

        return $this;
    }
}
