<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer country attribute source
 */
class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countriesFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->_countriesFactory = $countriesFactory;
        $this->state = $state;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
                $storeId = $this->getAttribute()->getWebsite() &&
                    $this->getAttribute()->getWebsite()->getDefaultStore() ?
                    $this->getAttribute()->getWebsite()->getDefaultStore()->getId() : Store::DEFAULT_STORE_ID;
            } else {
                $storeId = $this->getStoreManager()->getStore()->getId();
            }
            $this->_options = $this->_createCountriesCollection()->loadByStore($storeId)->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * Return new countries object
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected function _createCountriesCollection()
    {
        return $this->_countriesFactory->create();
    }

    /**
     * Retrieve Store Manager
     *
     * @deprecated 101.0.0
     * @see nothing
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (!$this->storeManager) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }
}
