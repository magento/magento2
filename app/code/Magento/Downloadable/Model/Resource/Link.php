<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Resource;

/**
 * Downloadable Product  Samples resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Link extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configuration;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configuration
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $configuration,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_catalogData = $catalogData;
        $this->_configuration = $configuration;
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($resource);
    }

    /**
     * Initialize connection and define resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('downloadable_link', 'link_id');
    }

    /**
     * Save title and price of link item
     *
     * @param \Magento\Downloadable\Model\Link $linkObject
     * @return $this
     */
    public function saveItemTitleAndPrice($linkObject)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $linkTitleTable = $this->getTable('downloadable_link_title');
        $linkPriceTable = $this->getTable('downloadable_link_price');

        $select = $writeAdapter->select()->from(
            $this->getTable('downloadable_link_title')
        )->where(
            'link_id=:link_id AND store_id=:store_id'
        );
        $bind = [':link_id' => $linkObject->getId(), ':store_id' => (int)$linkObject->getStoreId()];

        if ($writeAdapter->fetchOne($select, $bind)) {
            $where = ['link_id = ?' => $linkObject->getId(), 'store_id = ?' => (int)$linkObject->getStoreId()];
            if ($linkObject->getUseDefaultTitle()) {
                $writeAdapter->delete($linkTitleTable, $where);
            } else {
                $insertData = ['title' => $linkObject->getTitle()];
                $writeAdapter->update($linkTitleTable, $insertData, $where);
            }
        } else {
            if (!$linkObject->getUseDefaultTitle()) {
                $writeAdapter->insert(
                    $linkTitleTable,
                    [
                        'link_id' => $linkObject->getId(),
                        'store_id' => (int)$linkObject->getStoreId(),
                        'title' => $linkObject->getTitle()
                    ]
                );
            }
        }

        $select = $writeAdapter->select()->from($linkPriceTable)->where('link_id=:link_id AND website_id=:website_id');
        $bind = [':link_id' => $linkObject->getId(), ':website_id' => (int)$linkObject->getWebsiteId()];
        if ($writeAdapter->fetchOne($select, $bind)) {
            $where = ['link_id = ?' => $linkObject->getId(), 'website_id = ?' => $linkObject->getWebsiteId()];
            if ($linkObject->getUseDefaultPrice()) {
                $writeAdapter->delete($linkPriceTable, $where);
            } else {
                $writeAdapter->update($linkPriceTable, ['price' => $linkObject->getPrice()], $where);
            }
        } else {
            if (!$linkObject->getUseDefaultPrice()) {
                $dataToInsert[] = [
                    'link_id' => $linkObject->getId(),
                    'website_id' => (int)$linkObject->getWebsiteId(),
                    'price' => (double)$linkObject->getPrice(),
                ];
                if ($linkObject->getOrigData('link_id') != $linkObject->getLinkId()) {
                    $_isNew = true;
                } else {
                    $_isNew = false;
                }
                if ($linkObject->getWebsiteId() == 0 && $_isNew && !$this->_catalogData->isPriceGlobal()) {
                    $websiteIds = $linkObject->getProductWebsiteIds();
                    foreach ($websiteIds as $websiteId) {
                        $baseCurrency = $this->_configuration->getValue(
                            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                            'default'
                        );
                        $websiteCurrency = $this->_storeManager->getWebsite($websiteId)->getBaseCurrencyCode();
                        if ($websiteCurrency == $baseCurrency) {
                            continue;
                        }
                        $rate = $this->_createCurrency()->load($baseCurrency)->getRate($websiteCurrency);
                        if (!$rate) {
                            $rate = 1;
                        }
                        $newPrice = $linkObject->getPrice() * $rate;
                        $dataToInsert[] = [
                            'link_id' => $linkObject->getId(),
                            'website_id' => (int)$websiteId,
                            'price' => $newPrice,
                        ];
                    }
                }
                $writeAdapter->insertMultiple($linkPriceTable, $dataToInsert);
            }
        }
        return $this;
    }

    /**
     * Delete data by item(s)
     *
     * @param \Magento\Downloadable\Model\Link|array|int $items
     * @return $this
     */
    public function deleteItems($items)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $where = [];
        if ($items instanceof \Magento\Downloadable\Model\Link) {
            $where = ['link_id = ?' => $items->getId()];
        } elseif (is_array($items)) {
            $where = ['link_id in (?)' => $items];
        } else {
            $where = ['sample_id = ?' => $items];
        }
        if ($where) {
            $writeAdapter->delete($this->getMainTable(), $where);
            $writeAdapter->delete($this->getTable('downloadable_link_title'), $where);
            $writeAdapter->delete($this->getTable('downloadable_link_price'), $where);
        }
        return $this;
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $ifNullDefaultTitle = $adapter->getIfNullSql('st.title', 's.title');
        $select = $adapter->select()->from(
            ['m' => $this->getMainTable()],
            null
        )->join(
            ['s' => $this->getTable('downloadable_link_title')],
            's.link_id=m.link_id AND s.store_id=0',
            []
        )->joinLeft(
            ['st' => $this->getTable('downloadable_link_title')],
            'st.link_id=m.link_id AND st.store_id=:store_id',
            ['title' => $ifNullDefaultTitle]
        )->where(
            'm.product_id=:product_id'
        );
        $bind = [':store_id' => (int)$storeId, ':product_id' => $productId];

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * @return \Magento\Directory\Model\Currency
     */
    protected function _createCurrency()
    {
        return $this->_currencyFactory->create();
    }
}
