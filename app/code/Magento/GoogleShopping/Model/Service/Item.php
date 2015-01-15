<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Service;

/**
 * Google Content Item Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\GoogleShopping\Model\Service
{
    /**
     * @var \Magento\GoogleShopping\Helper\Data|null
     */
    protected $_googleShoppingHelper = null;

    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|null
     */
    protected $_date;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\Framework\Gdata\Gshopping\ContentFactory $contentFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\GoogleShopping\Helper\Data $googleShoppingHelper
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Framework\Gdata\Gshopping\ContentFactory $contentFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\GoogleShopping\Helper\Data $googleShoppingHelper,
        array $data = []
    ) {
        $this->_date = $date;
        $this->_googleShoppingHelper = $googleShoppingHelper;
        parent::__construct($logger, $coreRegistry, $config, $contentFactory, $data);
    }

    /**
     * Return Store level Service Instance
     *
     * @param int $storeId
     * @return \Magento\Framework\Gdata\Gshopping\Content
     */
    public function getService($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }
        return parent::getService($storeId);
    }

    /**
     * Insert Item into Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return $this
     */
    public function insert($item)
    {
        $service = $this->getService();
        $entry = $service->newEntry();
        $type = $item->getType();
        $type->convertProductToEntry($item->getProduct(), $entry);

        $entry = $service->insertItem($entry);
        $published = $this->convertContentDateToTimestamp($entry->getPublished()->getText());

        $item->setGcontentItemId($entry->getId())->setPublished($published);

        $expires = $this->_getAttributeValue($entry, 'expiration_date');
        if ($expires) {
            $expires = $this->convertContentDateToTimestamp($expires);
            $item->setExpires($expires);
        }
        return $this;
    }

    /**
     * Update Item data in Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return $this
     */
    public function update($item)
    {
        $service = $this->getService();
        $query = $this->_buildItemQuery($item);
        $entry = $service->getItem($query);

        $stats = $this->_getEntryStats($entry);
        if (isset($stats['expires'])) {
            $item->setExpires($stats['expires']);
        }
        $entry = $item->getType()->convertProductToEntry($item->getProduct(), $entry);
        $entry = $service->updateItem($entry);

        return $this;
    }

    /**
     * Delete Item from Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return $this
     */
    public function delete($item)
    {
        $service = $this->getService();
        $query = $this->_buildItemQuery($item);
        $service->delete($query->getQueryUrl());

        return $this;
    }

    /**
     * Convert Google Content date format to unix timestamp
     * Ex. 2008-12-08T16:57:23Z -> 2008-12-08 16:57:23
     *
     * @param string $gContentDate Google Content datetime
     * @return int
     */
    public function convertContentDateToTimestamp($gContentDate)
    {
        return $this->_date->date(null, $gContentDate);
    }

    /**
     * Return Google Content Item Attribute Value
     *
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @param string $name Google Content attribute name
     * @return string|null Attribute value
     */
    protected function _getAttributeValue($entry, $name)
    {
        $attribute = $entry->getContentAttributeByName($name);
        return $attribute instanceof \Magento\Framework\Gdata\Gshopping\Extension\Attribute ? $attribute->text : null;
    }

    /**
     * Retrieve item query for Google Content
     *
     * @param \Magento\GoogleShopping\Model\Item $item
     * @return \Magento\Framework\Gdata\Gshopping\ItemQuery
     */
    protected function _buildItemQuery($item)
    {
        $storeId = $item->getStoreId();
        $service = $this->getService($storeId);

        $countryInfo = $this->getConfig()->getTargetCountryInfo($storeId);
        $itemId = $this->_googleShoppingHelper->buildContentProductId($item->getProductId(), $item->getStoreId());

        $query = $service->newItemQuery()->setId(
            $itemId
        )->setTargetCountry(
            $this->getConfig()->getTargetCountry($storeId)
        )->setLanguage(
            $countryInfo['language']
        );

        return $query;
    }

    /**
     * Return item stats array based on Zend Gdata Entry object
     *
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return array
     */
    protected function _getEntryStats($entry)
    {
        $result = [];
        $expirationDate = $entry->getContentAttributeByName('expiration_date');
        if ($expirationDate instanceof \Magento\Framework\Gdata\Gshopping\Extension\Attribute) {
            $result['expires'] = $this->convertContentDateToTimestamp($expirationDate->text);
        }

        return $result;
    }
}
