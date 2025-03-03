<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides export configuration
 */
class Config extends \Magento\Framework\Config\Data implements \Magento\ImportExport\Model\Export\ConfigInterface
{
    /**
     * Constructor
     *
     * @param \Magento\ImportExport\Model\Export\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface $serializer
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        \Magento\ImportExport\Model\Export\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'export_config_cache',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Retrieve export entities configuration
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->get('entities');
    }

    /**
     * Retrieve export entity types configuration
     *
     * @param string $entity
     * @return array
     */
    public function getEntityTypes($entity)
    {
        $entities = $this->getEntities();
        return $entities[$entity]['types'] ?? [];
    }

    /**
     * Retrieve export file formats configuration
     *
     * @return array
     */
    public function getFileFormats()
    {
        return $this->get('fileFormats');
    }
}
