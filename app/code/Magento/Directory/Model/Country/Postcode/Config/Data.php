<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode\Config;

use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * Data constructor
     *
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Directory\Model\Country\Postcode\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, 'country_postcodes', $serializer);
    }
}
