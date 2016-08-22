<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer country attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countriesFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory
    ) {
        $this->_countriesFactory = $countriesFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_createCountriesCollection()->loadByStore(
                $this->resolveStoreId()
            )->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @deprecated
     * @return \Magento\Backend\Model\Session\Quote
     */
    private function getBackendSession()
    {
        return ObjectManager::getInstance()->get(\Magento\Backend\Model\Session\Quote::class);
    }

    /**
     * Retrieve store id in view of backend quote.
     * @return int
     */
    private function resolveStoreId()
    {
        $backendSession = $this->getBackendSession();
        if ($backendSession->getQuoteId() && $backendSession->getQuote()->hasStoreId()) {
            return $backendSession->getQuote()->getStoreId();
        }

        return $this->getStoreManager()->getStore()->getId();
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected function _createCountriesCollection()
    {
        return $this->_countriesFactory->create();
    }

    /**
     * @deprecated
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        return ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }
}
