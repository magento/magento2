<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer country with website specified attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\CountryHandlerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CountryWithWebsites extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countriesFactory;

    /**
     * @var \Magento\Customer\Model\CountryHandler
     */
    private $countryHandler;

    /**
     * @var array
     */
    private $options;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * CountryWithWebsites constructor.
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory
     * @param \Magento\Directory\Model\CountryHandlerInterface $countryHandler
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countriesFactory,
        \Magento\Directory\Model\CountryHandlerInterface $countryHandler,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->countriesFactory = $countriesFactory;
        $this->countryHandler = $countryHandler;
        $this->storeManager = $storeManager;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->options) {
            $allowedCountries = [];
            $websiteIds = [];

            foreach ($this->storeManager->getWebsites() as $website) {
                $countries = $this->countryHandler
                    ->getAllowedCountries($website->getId(), ScopeInterface::SCOPE_WEBSITE, true);
                $allowedCountries = array_merge($allowedCountries, $countries);

                foreach ($countries as $countryCode) {
                    $websiteIds[$countryCode][] = $website->getId();
                }
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
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private function createCountriesCollection()
    {
        return $this->countriesFactory->create();
    }
}
