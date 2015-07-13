<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for buyer countries supported by PayPal
 */
class BuyerCountry implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Directory\Model\Resource\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
    ) {
        $this->_configFactory = $configFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($isMultiselect = false)
    {
        $supported = $this->_configFactory->create()->getSupportedBuyerCountryCodes();
        $options = $this->_countryCollectionFactory->create()->addCountryCodeFilter(
            $supported,
            'iso2'
        )->loadData()->toOptionArray(
            $isMultiselect ? false : __('--Please Select--')
        );

        return $options;
    }
}
