<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Helper;

use Magento\BraintreeTwo\Model\Adminhtml\System\Config\Country as CountryConfig;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

/**
 * Class Country
 * @package Magento\BraintreeTwo\Helper
 */
class Country
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $countries;

    /**
     * @param CollectionFactory $factory
     */
    public function __construct(CollectionFactory $factory)
    {
        $this->collectionFactory = $factory;
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
                ->addFieldToFilter('country_id', ['nin' => CountryConfig::$excludedCountries])
                ->loadData()
                ->toOptionArray(false);
        }
        return $this->countries;
    }
}
