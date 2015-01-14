<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer country attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Resource\Address\Attribute\Source;

class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\Resource\Country\CollectionFactory
     */
    protected $_countriesFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countriesFactory
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countriesFactory
    ) {
        $this->_countriesFactory = $countriesFactory;
        parent::__construct($coreData, $attrOptionCollectionFactory, $attrOptionFactory);
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
                $this->getAttribute()->getStoreId()
            )->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \Magento\Directory\Model\Resource\Country\Collection
     */
    protected function _createCountriesCollection()
    {
        return $this->_countriesFactory->create();
    }
}
