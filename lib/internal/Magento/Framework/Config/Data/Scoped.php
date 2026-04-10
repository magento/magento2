<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides scoped configuration
 * @api
 * @since 100.0.2
 */
class Scoped extends \Magento\Framework\Config\Data
{
    /**
     * Configuration scope resolver
     *
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * Scope priority loading scheme
     *
     * @var string[]
     */
    protected $_scopePriorityScheme = [];

    /**
     * @var array
     */
    protected $_loadedScopes = [];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ConfigWriterInterface|null
     */
    private $configWriter;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @param ConfigWriterInterface|null $configWriter
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId,
        ?SerializerInterface $serializer = null,
        ?ConfigWriterInterface $configWriter = null
    ) {
        $this->_reader = $reader;
        $this->_configScope = $configScope;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->configWriter = $configWriter;
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return array|mixed|null
     */
    public function get($path = null, $default = null)
    {
        $this->_loadScopedData();
        return parent::get($path, $default);
    }

    /**
     * Load data for current scope
     *
     * @return void
     */
    protected function _loadScopedData()
    {
        $scope = $this->_configScope->getCurrentScope() ?? '';
        if (false == isset($this->_loadedScopes[$scope])) {
            if (false == in_array($scope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            foreach ($this->_scopePriorityScheme as $scopeCode) {
                if (false == isset($this->_loadedScopes[$scopeCode])) {
                    if ($scopeCode !== 'primary') {
                        $data = $this->loadScopeData($scopeCode);
                    } else {
                        $data = $this->_reader->read($scopeCode);
                    }
                    $this->merge($data);
                    $this->_loadedScopes[$scopeCode] = true;
                }
                if ($scopeCode == $scope) {
                    break;
                }
            }
        }
    }

    /**
     * Load data for a specific scope, using compiled file, cache backend, or reader
     *
     * @param string $scopeCode
     * @return array
     */
    private function loadScopeData(string $scopeCode): array
    {
        $cacheKey = $scopeCode . '::' . $this->_cacheId;

        if ($this->configWriter && $this->isCompiledConfigAvailable($cacheKey)) {
            return $this->loadCompiledConfig($cacheKey);
        }

        $data = $this->_cache->load($cacheKey);

        if ($data !== false) {
            $data = $this->serializer->unserialize($data);
        } else {
            $data = $this->_reader->read($scopeCode);
            $this->_cache->save($this->serializer->serialize($data), $cacheKey);
        }

        if ($this->configWriter) {
            $this->configWriter->write($cacheKey, $data);
        }

        return $data;
    }
}
