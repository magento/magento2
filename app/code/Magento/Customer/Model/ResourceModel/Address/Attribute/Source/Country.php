<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer country attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Country.
 * @package Magento\Customer\Model\ResourceModel\Address\Attribute\Source
 * @since 2.0.0
 */
class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @since 2.0.0
     */
    protected $_countriesFactory;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_createCountriesCollection()->loadByStore(
                $this->getStoreManager()->getStore()->getId()
            )->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     * @since 2.0.0
     */
    protected function _createCountriesCollection()
    {
        return $this->_countriesFactory->create();
    }

    /**
     * Retrieve Store Manager
     * @deprecated 2.2.0
     * @return StoreManagerInterface
     * @since 2.2.0
     */
    private function getStoreManager()
    {
        if (!$this->storeManager) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }
}
