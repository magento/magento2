<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\Config\Converter;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * UI Component configuration data
 * @since 2.2.0
 */
class Data implements \Magento\Framework\Config\DataInterface
{
    /**
     * ID in the storage cache
     */
    const CACHE_ID = 'ui_component_configuration_data';

    /**
     * Search pattern
     */
    const SEARCH_PATTERN = '%s.xml';

    /**
     * Config data
     *
     * @var array
     * @since 2.2.0
     */
    private $data = [];

    /**
     * @var ReaderFactory
     * @since 2.2.0
     */
    private $readerFactory;

    /**
     * @var CacheInterface
     * @since 2.2.0
     */
    private $cache;

    /**
     * @var string
     * @since 2.2.0
     */
    private $cacheId;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var string
     * @since 2.2.0
     */
    private $componentName;

    /**
     * Argument interpreter.
     *
     * @var InterpreterInterface
     * @since 2.2.0
     */
    private $argumentInterpreter;

    /**
     * @param string $componentName
     * @param ReaderFactory $readerFactory
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param InterpreterInterface $argumentInterpreter,
     * @since 2.2.0
     */
    public function __construct(
        $componentName,
        ReaderFactory $readerFactory,
        CacheInterface $cache,
        SerializerInterface $serializer,
        InterpreterInterface $argumentInterpreter
    ) {
        $this->readerFactory = $readerFactory;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->componentName = $componentName;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->cacheId = static::CACHE_ID . '_' . $componentName;
    }

    /**
     * Initialise data for configuration
     *
     * @return void
     * @since 2.2.0
     */
    private function initData()
    {
        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            /** @var Reader $reader */
            $reader = $this->readerFactory->create(
                ['fileName' => sprintf(self::SEARCH_PATTERN, $this->componentName)]
            );
            $data = $reader->read();
            $this->cache->save($this->serializer->serialize($data), $this->cacheId);
        } else {
            $data = $this->serializer->unserialize($data);
        }

        if (!empty($data)) {
            $this->data[$this->componentName] = [Converter::DATA_ATTRIBUTES_KEY => ['name' => $this->componentName]];
            $this->merge([$this->componentName => $data]);
            $this->data = $this->evaluateComponentArguments($this->data);
        }
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     * @since 2.2.0
     */
    public function merge(array $config)
    {
        $this->data = array_replace_recursive($this->get(), $config);
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     * @since 2.2.0
     */
    public function get($path = null, $default = null)
    {
        if (empty($this->data)) {
            $this->initData();
        }
        if ($path === null) {
            return $this->data;
        }
        $keys = explode('/', $path);
        $data = $this->data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Evaluated components arguments recursively
     *
     * @param array $components
     * @return array
     * @since 2.2.0
     */
    private function evaluateComponentArguments($components)
    {
        foreach ($components as &$component) {
            foreach ($component[Converter::DATA_ARGUMENTS_KEY] as $argumentName => $argument) {
                $component[Converter::DATA_ARGUMENTS_KEY][$argumentName] =
                    $this->argumentInterpreter->evaluate($argument);
            }
            $component[Converter::DATA_COMPONENTS_KEY] = $this->evaluateComponentArguments(
                $component[Converter::DATA_COMPONENTS_KEY]
            );
        }

        return $components;
    }
}
