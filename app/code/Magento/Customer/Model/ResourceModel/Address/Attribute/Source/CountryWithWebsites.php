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
use Magento\Customer\Model\Config\Share as CustomerShareConfig;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory as AttrubuteOptionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Return allowed countries for specified website
 */
class CountryWithWebsites extends Table
{
    /**
     * @var CountryCollectionFactory
     */
    private $countriesFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var array
     */
    private $options;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param OptionCollectionFactory $attrOptionCollectionFactory
     * @param AttrubuteOptionFactory $attrOptionFactory
     * @param CountryCollectionFactory $countriesFactory
     * @param AllowedCountries $allowedCountriesReader
     * @param StoreManagerInterface $storeManager
     * @param Share $shareConfig
     * @param Http|null $request
     * @param CustomerRepositoryInterface|null $customerRepository
     */
    public function __construct(
        OptionCollectionFactory $attrOptionCollectionFactory,
        AttrubuteOptionFactory $attrOptionFactory,
        CountryCollectionFactory $countriesFactory,
        AllowedCountries $allowedCountriesReader,
        StoreManagerInterface $storeManager,
        CustomerShareConfig $shareConfig,
        ?Http $request = null,
        ?CustomerRepositoryInterface $customerRepository = null
    ) {
        $this->countriesFactory = $countriesFactory;
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->storeManager = $storeManager;
        $this->shareConfig = $shareConfig;
        $this->request = $request
            ?? ObjectManager::getInstance()->get(Http::class);
        $this->customerRepository = $customerRepository
            ?? ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->options) {
            $websiteIds = [];

            if (!$this->shareConfig->isGlobalScope()) {
                $allowedCountries = [];

                foreach ($this->storeManager->getWebsites() as $website) {
                    $countries = $this->allowedCountriesReader
                        ->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId());
                    $allowedCountries[] = $countries;

                    foreach ($countries as $countryCode) {
                        $websiteIds[$countryCode][] = $website->getId();
                    }
                }

                $allowedCountries = array_unique(array_merge([], ...$allowedCountries));
            } else {
                // Address can be added only for the allowed country list.
                $websiteId = null;
                $customerId = $this->request->getParam('parent_id') ?? null;
                if ($customerId) {
                    $customer = $this->customerRepository->getById($customerId);
                    $websiteId = $customer->getWebsiteId();
                }

                $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                );
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
     * @return CountryCollection
     */
    private function createCountriesCollection()
    {
        return $this->countriesFactory->create();
    }
}
