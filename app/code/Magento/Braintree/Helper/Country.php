<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Helper;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Braintree\Model\Adminhtml\System\Config\Country as CountryConfig;

/**
 * Class Country
 * @since 2.1.0
 */
class Country
{
    /**
     * @var CollectionFactory
     * @since 2.1.0
     */
    private $collectionFactory;

    /**
     * @var CountryConfig
     * @since 2.1.0
     */
    private $countryConfig;

    /**
     * @var array
     * @since 2.1.0
     */
    private $countries;

    /**
     * @param CollectionFactory $factory
     * @param CountryConfig $countryConfig
     * @since 2.1.0
     */
    public function __construct(CollectionFactory $factory, CountryConfig $countryConfig)
    {
        $this->collectionFactory = $factory;
        $this->countryConfig = $countryConfig;
    }

    /**
     * Returns countries array
     *
     * @return array
     * @since 2.1.0
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
