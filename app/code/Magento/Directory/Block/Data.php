<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Block;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directoryHelper = $directoryHelper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_configCacheType = $configCacheType;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * @return string
     */
    public function getLoadrRegionUrl()
    {
        return $this->getUrl('directory/json/childRegion');
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if ($collection === null) {
            $collection = $this->_countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }

        return $collection;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * @param null|string $defValue
     * @param string $name
     * @param string $id
     * @param string $title
     * @return string
     */
    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        if ($defValue === null) {
            $defValue = $this->getCountryId();
        }
        $cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        $cache = $this->_configCacheType->load($cacheKey);
        if ($cache) {
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
            $this->_configCacheType->save($this->getSerializer()->serialize($options), $cacheKey);
        }
        $html = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setName(
            $name
        )->setId(
            $id
        )->setTitle(
            __($title)
        )->setValue(
            $defValue
        )->setOptions(
            $options
        )->setExtraParams(
            'data-validate="{\'validate-select\':true}"'
        )->getHtml();

        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $html;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    public function getRegionCollection()
    {
        $collection = $this->getData('region_collection');
        if ($collection === null) {
            $collection = $this->_regionCollectionFactory->create()->addCountryFilter($this->getCountryId())->load();

            $this->setData('region_collection', $collection);
        }
        return $collection;
    }

    /**
     * @return string
     */
    public function getRegionHtmlSelect()
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        $cacheKey = 'DIRECTORY_REGION_SELECT_STORE' . $this->_storeManager->getStore()->getId();
        $cache = $this->_configCacheType->load($cacheKey);
        if ($cache) {
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            $options = $this->getRegionCollection()->toOptionArray();
            $this->_configCacheType->save($this->getSerializer()->serialize($options), $cacheKey);
        }
        $html = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setName(
            'region'
        )->setTitle(
            __('State/Province')
        )->setId(
            'state'
        )->setClass(
            'required-entry validate-state'
        )->setValue(
            (int)$this->getRegionId()
        )->setOptions(
            $options
        )->getHtml();
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        return $html;
    }

    /**
     * @return string
     */
    public function getCountryId()
    {
        $countryId = $this->getData('country_id');
        if ($countryId === null) {
            $countryId = $this->directoryHelper->getDefaultCountry();
        }
        return $countryId;
    }

    /**
     * @return string
     */
    public function getRegionsJs()
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        $regionsJs = $this->getData('regions_js');
        if (!$regionsJs) {
            $countryIds = [];
            foreach ($this->getCountryCollection() as $country) {
                $countryIds[] = $country->getCountryId();
            }
            $collection = $this->_regionCollectionFactory->create()->addCountryFilter($countryIds)->load();
            $regions = [];
            foreach ($collection as $region) {
                if (!$region->getRegionId()) {
                    continue;
                }
                $regions[$region->getCountryId()][$region->getRegionId()] = [
                    'code' => $region->getCode(),
                    'name' => $region->getName(),
                ];
            }
            $regionsJs = $this->_jsonEncoder->encode($regions);
        }
        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $regionsJs;
    }

    /**
     * Get serializer
     *
     * @return \Magento\Framework\Serialize\SerializerInterface
     * @deprecated 100.2.0
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }
        return $this->serializer;
    }
}
