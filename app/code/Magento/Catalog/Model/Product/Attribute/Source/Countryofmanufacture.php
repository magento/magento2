<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product country attribute source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Countryofmanufacture extends AbstractSource implements OptionSourceInterface
{
    /**
     * @var Config
     */
    protected $_configCacheType;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * Construct
     *
     * @param CountryFactory $countryFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $configCacheType
     * @param ResolverInterface $localeResolver
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CountryFactory $countryFactory,
        StoreManagerInterface $storeManager,
        Config $configCacheType,
        ResolverInterface $localeResolver,
        SerializerInterface $serializer
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->_configCacheType = $configCacheType;
        $this->localeResolver = $localeResolver;
        $this->serializer = $serializer;
    }

    /**
     * Get list of all available countries
     *
     * @return array
     */
    public function getAllOptions()
    {
        $storeCode = $this->_storeManager->getStore()->getCode();
        $locale = $this->localeResolver->getLocale();

        $cacheKey = 'COUNTRYOFMANUFACTURE_SELECT_STORE_' . $storeCode . '_LOCALE_' . $locale;
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
