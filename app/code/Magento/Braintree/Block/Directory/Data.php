<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Directory;

class Data extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $braintreeHelper;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country
     */
    protected $countrySource;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Braintree\Helper\Data $braintreeHelper
     * @param \Magento\Braintree\Model\System\Config\Source\Country $countrySource
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Braintree\Helper\Data $braintreeHelper,
        \Magento\Braintree\Model\System\Config\Source\Country $countrySource,
        \Magento\Braintree\Model\Config\Cc $config,
        array $data = []
    ) {
        $this->braintreeHelper = $braintreeHelper;
        $this->countrySource = $countrySource;
        $this->config = $config;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepares html with countries
     *
     * @param string $defValue
     * @param string $name
     * @param string $id
     * @param string $title
     * @return string
     */
    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        if (!($defValue)) {
            $defValue = $this->getCountryId();
        }

        $cacheId = 'BRAINTREE_DIRECTORY_COUNTRY_SELECT_STORE_'.$this->_storeManager->getStore()->getCode();
        if ($cache = $this->_configCacheType->load($cacheId)) {
            $options = unserialize($cache);
        } else {
            $options = $this->getCountryCollection()->toOptionArray(false);
        }
        $html = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName($name)
            ->setId($id)
            ->setTitle(__($title))
            ->setClass('validate-select')
            ->setValue($defValue)
            ->setOptions($options)
            ->getHtml();
        return $html;
    }

    /**
     * Loads country collection
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if ($collection == null) {
            $restrictedCountriesByBraintree = $this->countrySource->getRestrictedCountries();

            $collection = $this->_countryCollectionFactory->create()
                ->addFieldToFilter('country_id', ['nin' => $restrictedCountriesByBraintree])
                ->loadByStore();

            foreach ($collection as $item) {
                $countryCode=$item->getData('country_id');
                if (!$this->config->canUseForCountry($countryCode)) {
                    $restrictedCountriesByBraintree[]=$item->getData('country_id');
                }
            }

            $collection = $this->_countryCollectionFactory->create()
                ->addFieldToFilter('country_id', ['nin' => $restrictedCountriesByBraintree])
                ->loadByStore();

            $this->setData('country_collection', $collection);
        }
        return $collection;
    }

    /**
     * Retrieve the default country
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->directoryHelper->getDefaultCountry();
    }
}
