<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides import configuration
 */
class Config extends \Magento\Framework\Config\Data implements \Magento\ImportExport\Model\Import\ConfigInterface
{
    /**
     * Constructor
     *
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'import_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Retrieve import entities configuration
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->get('entities');
    }

    /**
     * Retrieve import entity types configuration
     *
     * @param string $entity
     * @return array
     */
    public function getEntityTypes($entity)
    {
        $entities = $this->getEntities();
        return isset($entities[$entity]) ? $entities[$entity]['types'] : [];
    }

    /**
     * Retrieve a list of indexes which are affected by import of the specified entity.
     *
     * @param string $entity
     * @return array
     */
    public function getRelatedIndexers($entity)
    {
        $entities = $this->getEntities();
        return isset($entities[$entity]) ? $entities[$entity]['relatedIndexers'] : [];
    }
}
