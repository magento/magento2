<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductTypes;

use Magento\Framework\Serialize\SerializerInterface;

class Config extends \Magento\Framework\Config\Data implements \Magento\Catalog\Model\ProductTypes\ConfigInterface
{
    /**
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Catalog\Model\ProductTypes\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'product_types_config',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Get configuration of product type by name
     *
     * @param string $name
     * @return array
     */
    public function getType($name)
    {
        return $this->get('types/' . $name, []);
    }

    /**
     * Get configuration of all registered product types
     *
     * @return array
     */
    public function getAll()
    {
        return $this->get('types');
    }

    /**
     * Check whether product type is set of products
     *
     * @param string $typeId
     * @return bool
     */
    public function isProductSet($typeId)
    {
        return 'true' == $this->get('types/' . $typeId . '/custom_attributes/is_product_set', false);
    }

    /**
     * Get composable types
     *
     * @return array
     */
    public function getComposableTypes()
    {
        return $this->get('composableTypes', []);
    }

    /**
     * Get list of product types that comply with condition
     *
     * @param string $attributeName
     * @param string $value
     * @return array
     */
    public function filter($attributeName, $value = 'true')
    {
        $availableProductTypes = [];
        foreach ($this->getAll() as $type) {
            if (!isset(
                $type['custom_attributes'][$attributeName]
            ) || $type['custom_attributes'][$attributeName] == $value
            ) {
                $availableProductTypes[$type['name']] = $type['name'];
            }
        }
        return $availableProductTypes;
    }
}
