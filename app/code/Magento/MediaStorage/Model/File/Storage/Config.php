<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaStorage\Model\File\Storage;

class Config
{
    const CACHE_ID = 'config';

    /**
     * Config cache file path
     *
     * @var string
     */
    protected $cacheFilePath;

    /**
     * Loaded config
     *
     * @var array
     */
    protected $config;

    /***
     * Allowd resources
     *
     * @var string[]
     */
    protected $allowedResources;

    /***
     * ConfigStorage
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Config serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Config Cache
     *
     * @var Cache
     */
    private $cache;


    /**
     * @param \Magento\MediaStorage\Model\File\Storage $storage
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\Cache\Type\MediaStorage
     */
    public function __construct(
        \Magento\MediaStorage\Model\File\Storage $storage,
        SerializerInterface $serializer = null,
        \Magento\MediaStorage\Model\Cache\Type\MediaStorage $cache
    ) {
        $this->cache = $cache;
        $this->storage = $storage;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getAllowedResources()
    {
        if (null === $this->allowedResources) {
            $allowedResources = $this->cache->load(self::CACHE_ID);
            if ($allowedResources && is_string($allowedResources)) {
                $this->allowedResources = $this->serializer->unserialize($allowedResources);
            } else {
                $this->allowedResources = $this->storage->getScriptConfig();
                $this->cache->save($this->serializer->serialize($this->allowedResources), self::CACHE_ID);
            }
        }
        return $this->allowedResources['allowed_resources'];
    }
}
