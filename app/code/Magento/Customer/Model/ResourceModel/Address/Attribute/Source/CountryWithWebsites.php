<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer country with website specified attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Customer\Model\Config\Share;
use Magento\Directory\Model\AllowedCountries;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites
 *
 * @since 2.1.3
 */
class CountryWithWebsites extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @since 2.1.3
     */
    private $countriesFactory;

    /**
     * @var \Magento\Directory\Model\AllowedCountries
     * @since 2.1.3
     */
    private $allowedCountriesReader;

    /**
     * @var array
     * @since 2.1.3
     */
    private $options;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.1.3
     */
    private $storeManager;

    /**
     * @var Share
     * @since 2.1.3
     */
    private $shareConfig;

    /**
     * CountryWithWebsites constructor.
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory
     * @param AllowedCountries $allowedCountriesReader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Share $shareConfig
     * @since 2.1.3
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory,
        \Magento\Directory\Model\AllowedCountries $allowedCountriesReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Config\Share $shareConfig
    ) {
        $this->countriesFactory = $countriesFactory;
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->storeManager = $storeManager;
        $this->shareConfig = $shareConfig;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve all options
     *
     * @return array
     * @since 2.1.3
     */
    public function getAllOptions()
    {
        if (!$this->options) {
            $allowedCountries = [];
            $websiteIds = [];

            if (!$this->shareConfig->isGlobalScope()) {
                foreach ($this->storeManager->getWebsites() as $website) {
                    $countries = $this->allowedCountriesReader
                        ->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId());
                    $allowedCountries = array_merge($allowedCountries, $countries);

                    foreach ($countries as $countryCode) {
                        $websiteIds[$countryCode][] = $website->getId();
                    }
                }
            } else {
                $allowedCountries = $this->allowedCountriesReader->getAllowedCountries();
            }

            $this->options = $this->createCountriesCollection()
                ->addFieldToFilter('country_id', ['in' => $allowedCountries])
                ->toOptionArray();

            foreach ($this->options as &$option) {
                if (isset($websiteIds[$option['value']])) {
                    $option['website_ids'] = $websiteIds[$option['value']];
                }
            }
        }

        return $this->options;
    }

    /**
     * Create Countries Collection with all countries
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     * @since 2.1.3
     */
    private function createCountriesCollection()
    {
        return $this->countriesFactory->create();
    }
}
