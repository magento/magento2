<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for buyer countries supported by PayPal
 * @since 2.0.0
 */
class BuyerCountry implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     * @since 2.0.0
     */
    protected $_configFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @since 2.0.0
     */
    protected $_countryCollectionFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    ) {
        $this->_configFactory = $configFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
