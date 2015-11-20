<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Elasticsearch index resource model
 */
class Index extends \Magento\AdvancedSearch\Model\ResourceModel\Index
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param Config $eavConfig
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        Config $eavConfig,
        $connectionName = null
    ) {
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $storeManager, $connectionName);
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

    /**
     * @param array $array
     * @param string $glue
     * @param bool $include_keys
     * @param bool $trim_all
     * @return string
     */
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
