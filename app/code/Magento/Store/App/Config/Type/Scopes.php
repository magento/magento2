<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\App\Config\Type;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

/**
 * Merge and hold scopes data from different sources
 */
class Scopes implements ConfigTypeInterface, ResetAfterRequestInterface
{
    /**
     * Configuration type registered in the application config.
     */
    public const CONFIG_TYPE = 'scopes';

    /**
     * Identifier for the serialized scopes aggregate.
     */
    public const CACHE_ID = 'scopes_config_type';

    /**
     * Aggregated runtime and deployment-file scopes source.
     *
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * Scope data loaded for the current request.
     *
     * @var DataObject[]
     */
    private $data;

    /**
     * Map between scope id and scope code
     *
     * @var array
     */
    private $idCodeMap = [];

    /**
     * The field names holder of scope id for specific scope pool.
     * Used for map id to code, e.g. websites/0 to websites/admin
     *
     * @var array
     */
    private $scopeIdField = [
        ScopeInterface::SCOPE_WEBSITES => 'website_id',
        ScopeInterface::SCOPE_STORES => 'store_id',
    ];

    /**
     * Configuration cache frontend.
     *
     * @var Config
     */
    private Config $cache;

    /**
     * Serializer for the cached scopes aggregate.
     *
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * Runtime source reporting whether database scopes were read.
     *
     * @var RuntimeConfigSource
     */
    private RuntimeConfigSource $runtimeConfigSource;

    /**
     * Initialize the aggregate source and optional cache services.
     *
     * @param ConfigSourceInterface $source
     * @param Config|null $cache
     * @param SerializerInterface|null $serializer
     * @param RuntimeConfigSource|null $runtimeConfigSource
     */
    public function __construct(
        ConfigSourceInterface $source,
        ?Config $cache = null,
        ?SerializerInterface $serializer = null,
        ?RuntimeConfigSource $runtimeConfigSource = null
    ) {
        $this->source = $source;
        $this->cache = $cache ?? ObjectManager::getInstance()->get(Config::class);
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->runtimeConfigSource = $runtimeConfigSource
            ?? ObjectManager::getInstance()->get(RuntimeConfigSource::class);
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        if (null === $this->data) {
            $this->data = new DataObject($this->loadData());
        }

        $patchChunks = explode('/', (string)$path);

        if (isset($patchChunks[1])
            && is_numeric($patchChunks[1])
            && in_array($patchChunks[0], [ScopeInterface::SCOPE_WEBSITES, ScopeInterface::SCOPE_STORES], true)
        ) {
            $path = $this->convertIdPathToCodePath($patchChunks);
        }

        return $this->data->getData($path);
    }

    /**
     * Load cached scopes or aggregate sources when a runtime read is required.
     *
     * @return array
     */
    private function loadData(): array
    {
        if (!$this->runtimeConfigSource->isDbAvailable()) {
            return $this->source->get();
        }

        $cachedData = $this->cache->load(self::CACHE_ID);
        if ($cachedData !== false) {
            return $this->serializer->unserialize($cachedData);
        }

        $data = $this->source->get();
        if ($this->runtimeConfigSource->hasRuntimeData()) {
            $this->cache->save(
                $this->serializer->serialize($data),
                self::CACHE_ID,
                [Store::CACHE_TAG, Group::CACHE_TAG, Website::CACHE_TAG]
            );
        }
        return $data;
    }

    /**
     * Replace scope id with scope code. E.g. path 'websites/admin' will be converted to 'websites/0'
     *
     * @param array $patchChunks
     * @return string
     */
    private function convertIdPathToCodePath(array $patchChunks)
    {
        list($scopePool, $scopeId) = $patchChunks;
        if (!isset($this->idCodeMap[$scopePool]) || !array_key_exists($scopeId, $this->idCodeMap[$scopePool])) {
            $scopeData = $this->data->getData($scopePool);
            foreach ((array)$scopeData as $scopeEntity) {
                if (!isset($scopeEntity[$this->scopeIdField[$scopePool]])) {
                    continue;
                }
                $this->idCodeMap[$scopePool][$scopeEntity[$this->scopeIdField[$scopePool]]] = $scopeEntity['code'];
            }

            if (!isset($this->idCodeMap[$scopePool][$scopeId])) {
                $this->idCodeMap[$scopePool][$scopeId] = null;
            }
        }

        if ($this->idCodeMap[$scopePool][$scopeId]) {
            $patchChunks[1] = $this->idCodeMap[$scopePool][$scopeId];
        }

        return implode('/', $patchChunks);
    }

    /**
     * Clear request-local scope data and identifier mappings.
     *
     * @return void
     */
    public function clean()
    {
        $this->_resetState();
    }

    /**
     * Release scope data and identifier mappings between requests.
     *
     * @return void
     */
    public function _resetState(): void
    {
        $this->data = null;
        $this->idCodeMap = [];
    }
}
