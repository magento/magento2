<?php declare(strict_types=1);
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

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;
use \Magento\Eav\Model\Entity\Collection\AbstractCollection;
use \Magento\Framework\Data\Collection;

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
     */
    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Cache\Type\Config $configCacheType
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->_configCacheType = $configCacheType;
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
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            /** @var \Magento\Directory\Model\Country $country */
            $country = $this->_countryFactory->create();
            /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
            $collection = $country->getResourceCollection();
            $options = $collection->load()->toOptionArray();
            $this->_configCacheType->save($this->getSerializer()->serialize($options), $cacheKey);
        }
        return $options;
    }

    /**
     * Get serializer
     *
     * @return \Magento\Framework\Serialize\SerializerInterface
     * @deprecated 101.1.0
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }
        return $this->serializer;
    }

    /**
     * Add Value Sort To Collection Select
     * all NULL values will add to the end
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir direction
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    public function addValueSortToCollection($collection, $dir = Collection::SORT_ORDER_DESC) : AbstractSource
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeId = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();
        $linkField = $this->getAttribute()->getEntity()->getLinkField();

        if ($this->getAttribute()->isScopeGlobal()) {
            $tableName = $attributeCode . '_t';

            $collection->getSelect()->joinLeft(
                [$tableName => $attributeTable],
                "e.{$linkField}={$tableName}.{$linkField}" .
                " AND {$tableName}.attribute_id='{$attributeId}'" .
                " AND {$tableName}.store_id='0'",
                []
            );

            $valueExpr = $tableName . '.value';
            $collection->getSelect()->order(
                [
                    "ISNULL($valueExpr)",
                    $valueExpr . ' ' . $dir
                ]
            );
        }
        else {
            $tableName = $attributeCode . '_t';
            $tableName2 = $attributeCode . '_t2';

            $collection->getSelect()->joinLeft(
                [$tableName => $attributeTable],
                "e.{$linkField}={$tableName}.{$linkField}" .
                " AND {$tableName}.attribute_id='{$attributeId}'" .
                " AND {$tableName}.store_id=0",
                []
            )->joinLeft(
                [$tableName2 => $attributeTable],
                "e.{$linkField}={$tableName2}.{$linkField}" .
                " AND {$tableName2}.attribute_id='{$attributeId}'" .
                " AND {$tableName2}.store_id='{$collection->getStoreId()}'",
                []
            );

            $valueExpr = $tableName . '.value';
            $collection->getSelect()->order(
                [
                    "ISNULL($valueExpr)",
                    $valueExpr . ' ' . $dir
                ]
            );
        }
        return parent::addValueSortToCollection($collection, $dir);
    }
}
