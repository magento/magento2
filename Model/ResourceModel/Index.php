<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
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
     * @param bool $includeKeys
     * @param bool $trimAll
     * @return string
     */
    private function recursiveImplode(array $array, $glue = ',', $includeKeys = false, $trimAll = true)
    {
        $gluedString = '';
        array_walk_recursive($array, function ($value, $key) use ($glue, $includeKeys, &$gluedString) {
            $includeKeys and $gluedString .= $key.$glue;
            $gluedString .= $value.$glue;
        });
        strlen($glue) > 0 and $gluedString = substr($gluedString, 0, -strlen($glue));
        $trimAll and $gluedString = preg_replace("/(\s)/ixsm", '', $gluedString);
        return (string) $gluedString;
    }
}
