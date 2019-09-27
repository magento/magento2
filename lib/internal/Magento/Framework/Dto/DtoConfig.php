<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Config\CacheInterface\Proxy as CacheInterface;
use Magento\Framework\Config\Data;
use Magento\Framework\Dto\Config\Reader\Proxy as Reader;
use Magento\Framework\Serialize\SerializerInterface\Proxy as SerializerInterface;

/**
 * DTO config
 */
class DtoConfig extends Data
{
    /**
     * Cache identifier
     */
    private const CACHE_ID = 'dto_config';

    /**
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId|null
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        $cacheId = self::CACHE_ID,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Return true if a class is defined in the dto.xml config
     *
     * @param string $name
     * @return bool
     */
    public function isDto(string $name): bool
    {
        $config = $config = $this->getConfiguration($name);
        return $config !== null;
    }

    /**
     * Return true if the given name represents a DTO interface
     *
     * @param string $name
     * @return bool
     */
    public function isInterface(string $name): bool
    {
        return (bool) $this->get('interface/' . $name, false);
    }

    /**
     * Get interface configuration for given DTO class/interface.
     *
     * @param string $name
     * @return array|null
     */
    public function getConfiguration(string $name): ?array
    {
        $interfaceName = $this->getInterfaceName($name);
        if ($interfaceName === null) {
            return null;
        }

        return $this->get('interface/' . $interfaceName);
    }

    /**
     * Get interface name for given DTO class.
     * If the name passed is an interface, the same value is returned.
     *
     * @param string $name
     * @return string|null
     */
    public function getInterfaceName(string $name): ?string
    {
        if ($this->isInterface($name)) {
            return $name;
        }

        return $this->get('class/' . $name);
    }

    /**
     * Return true if a class is defined as immutable DTO
     *
     * @param string $name
     * @return bool
     */
    public function isImmutable(string $name): bool
    {
        $config = $this->getConfiguration($name);
        if (!$config) {
            return false;
        }

        return !$config['mutable'];
    }
}
