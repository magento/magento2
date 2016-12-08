<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product country attribute source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;

class Countryofmanufacture extends AbstractSource implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Construct
     *
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer [optional]
     */
    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Serialize\SerializerInterface $serializer = null
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->_configCacheType = $configCacheType;
        $this->serializer = $serializer;
    }

    /**
     * Get list of all available countries
     *
     * @return array
     */
    public function getAllOptions()
    {
        $cacheKey = 'COUNTRYOFMANUFACTURE_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        if ($cache = $this->_configCacheType->load($cacheKey)) {
            $options = $this->serializer->unserialize($cache);
        } else {
            /** @var \Magento\Directory\Model\Country $country */
            $country = $this->_countryFactory->create();
            /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
            $collection = $country->getResourceCollection();
            $options = $collection->load()->toOptionArray();
            $this->_configCacheType->save($this->serializer->serialize($options), $cacheKey);
        }
        return $options;
    }
}
