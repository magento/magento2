<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Elasticsearch index resource model
 */
class Index extends Fulltext
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        Config $eavConfig,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $eventManager, $connectionName);
    }

    /**
     * Return array of price data per customer and website by products
     *
     * @param null|array $productIds
     * @return array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_index_price'),
            ['entity_id', 'customer_group_id', 'website_id', 'min_price']
        );

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }

        return $result;
    }

    /**
     * Retrieve price data for product
     *
     * @param null|array $productIds
     * @param int $storeId
     * @return array
     */
    public function getPriceIndexData($productIds, $storeId)
    {
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);

        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if (!isset($priceProductsIndexData[$websiteId])) {
            return [];
        }

        return $priceProductsIndexData[$websiteId];
    }

    /**
     * Prepare system index data for products.
     *
     * @param int $storeId
     * @param null|array $productIds
     * @return array
     */
    public function getCategoryProductIndexData($storeId = null, $productIds = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            [$this->getTable('catalog_category_product_index')],
            ['category_id', 'product_id', 'position', 'store_id']
        )->where(
            'store_id = ?',
            $storeId
        );

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }

        return $result;
    }

    /**
     * Retrieve moved categories product ids
     *
     * @param int $categoryId
     * @return array
     */
    public function getMovedCategoryProductIds($categoryId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->distinct()->from(
            ['c_p' => $this->getTable('catalog_category_product')],
            ['product_id']
        )->join(
            ['c_e' => $this->getTable('catalog_category_entity')],
            'c_p.category_id = c_e.entity_id',
            []
        )->where(
            $connection->quoteInto('c_e.path LIKE ?', '%/' . $categoryId . '/%')
        )->orWhere(
            'c_p.category_id = ?',
            $categoryId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Retrieve all attributes for given product ids
     *
     * @param array $productIds
     * @return array
     */
    public function getFullProductIndexData(array $productIds)
    {
        foreach ($productIds as $productId) {
            $product = $this->productRepository->getById($productId);
            $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
            $productAttributesWithValues = $product->getData();
            foreach ($productAttributesWithValues as $attributeCode => $value) {
                if (in_array($attributeCode, $attributeCodes)) {
                    if (is_array($value)) {
                        $implodedValue = $this->recursiveImplode($value, ',');
                        $productAttributes[$productId][$attributeCode] =  $implodedValue;
                    } else {
                        $productAttributes[$productId][$attributeCode] =  $value;
                    }
                }
            }
        }
        return $productAttributes;
    }

    private function recursiveImplode(array $array, $glue = ',', $include_keys = false, $trim_all = true)
    {
        $glued_string = '';
        array_walk_recursive($array, function ($value, $key) use ($glue, $include_keys, &$glued_string) {
            $include_keys and $glued_string .= $key.$glue;
            $glued_string .= $value.$glue;
        });
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));
        $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
        return (string) $glued_string;
    }
}
