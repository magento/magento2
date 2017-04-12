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

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Country.
 * @package Magento\Customer\Model\ResourceModel\Address\Attribute\Source
 */
class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countriesFactory;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

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
                $this->getStoreResolver()->getCurrentStoreId()
            )->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected function _createCountriesCollection()
    {
        return $this->_countriesFactory->create();
    }

    /**
     * Retrieve Store Resolver
     * @deprecated
     * @return StoreResolverInterface
     */
    private function getStoreResolver()
    {
        if (!$this->storeResolver) {
            $this->storeResolver = ObjectManager::getInstance()->get(StoreResolverInterface::class);
        }

        return $this->storeResolver;
    }
}
