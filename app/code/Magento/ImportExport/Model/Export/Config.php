<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides export configuration
 * @since 2.0.0
 */
class Config extends \Magento\Framework\Config\Data implements \Magento\ImportExport\Model\Export\ConfigInterface
{
    /**
     * Constructor
     *
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\ImportExport\Model\Export\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'export_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Retrieve export entities configuration
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getEntityTypes($entity)
    {
        $entities = $this->getEntities();
        return isset($entities[$entity]) ? $entities[$entity]['types'] : [];
    }

    /**
     * Retrieve export file formats configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getFileFormats()
    {
        return $this->get('fileFormats');
    }
}
