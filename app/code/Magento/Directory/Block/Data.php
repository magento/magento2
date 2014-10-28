<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory data block
 */
namespace Magento\Directory\Block;

class Data extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Directory\Model\Resource\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * @var \Magento\Directory\Model\Resource\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_coreData = $coreData;
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
     * @return \Magento\Directory\Model\Resource\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if (is_null($collection)) {
            $collection = $this->_countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }

        return $collection;
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
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, array('group' => 'TEST', 'method' => __METHOD__));
        if (is_null($defValue)) {
            $defValue = $this->getCountryId();
        }
        $cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        $cache = $this->_configCacheType->load($cacheKey);
        if ($cache) {
            $options = unserialize($cache);
        } else {
            $options = $this->getCountryCollection()->toOptionArray();
            $this->_configCacheType->save(serialize($options), $cacheKey);
        }
        $html = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
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
     * @return \Magento\Directory\Model\Resource\Region\Collection
     */
    public function getRegionCollection()
    {
        $collection = $this->getData('region_collection');
        if (is_null($collection)) {
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
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, array('group' => 'TEST', 'method' => __METHOD__));
        $cacheKey = 'DIRECTORY_REGION_SELECT_STORE' . $this->_storeManager->getStore()->getId();
        $cache = $this->_configCacheType->load($cacheKey);
        if ($cache) {
            $options = unserialize($cache);
        } else {
            $options = $this->getRegionCollection()->toOptionArray();
            $this->_configCacheType->save(serialize($options), $cacheKey);
        }
        $html = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setName(
            'region'
        )->setTitle(
            __('State/Province')
        )->setId(
            'state'
        )->setClass(
            'required-entry validate-state'
        )->setValue(
            intval($this->getRegionId())
        )->setOptions(
            $options
        )->getHtml();
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, array('group' => 'TEST', 'method' => __METHOD__));
        return $html;
    }

    /**
     * @return string
     */
    public function getCountryId()
    {
        $countryId = $this->getData('country_id');
        if (is_null($countryId)) {
            $countryId = $this->_coreData->getDefaultCountry();
        }
        return $countryId;
    }

    /**
     * @return string
     */
    public function getRegionsJs()
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, array('group' => 'TEST', 'method' => __METHOD__));
        $regionsJs = $this->getData('regions_js');
        if (!$regionsJs) {
            $countryIds = array();
            foreach ($this->getCountryCollection() as $country) {
                $countryIds[] = $country->getCountryId();
            }
            $collection = $this->_regionCollectionFactory->create()->addCountryFilter($countryIds)->load();
            $regions = array();
            foreach ($collection as $region) {
                if (!$region->getRegionId()) {
                    continue;
                }
                $regions[$region->getCountryId()][$region->getRegionId()] = array(
                    'code' => $region->getCode(),
                    'name' => $region->getName()
                );
            }
            $regionsJs = $this->_jsonEncoder->encode($regions);
        }
        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $regionsJs;
    }
}
