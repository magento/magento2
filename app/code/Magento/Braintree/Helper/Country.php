<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Helper;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

/**
 * Class Country
 */
class Country
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Braintree\Model\Adminhtml\System\Config\Country
     */
    private $countryConfig;

    /**
     * @var array
     */
    private $countries;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $factory
     * @param \Magento\Braintree\Model\Adminhtml\System\Config\Country $countryConfig
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $factory,
        \Magento\Braintree\Model\Adminhtml\System\Config\Country $countryConfig
    ) {
        $this->collectionFactory = $factory;
        $this->countryConfig = $countryConfig;
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getCountries()
    {
        if (!$this->countries) {
            $this->countries = $this->collectionFactory->create()
                ->addFieldToFilter('country_id', ['nin' => $this->countryConfig->getExcludedCountries()])
                ->loadData()
                ->toOptionArray(false);
        }
        return $this->countries;
    }
}
