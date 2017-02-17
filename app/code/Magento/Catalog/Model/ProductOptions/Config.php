<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductOptions;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides product options configuration
 */
class Config extends \Magento\Framework\Config\Data implements
    \Magento\Catalog\Model\ProductOptions\ConfigInterface
{
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ProductOptions\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Catalog\Model\ProductOptions\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'product_options_config',
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
    public function getOption($name)
    {
        return $this->get($name, []);
    }

    /**
     * Get configuration of all registered product types
     *
     * @return array
     */
    public function getAll()
    {
        return $this->get();
    }
}
