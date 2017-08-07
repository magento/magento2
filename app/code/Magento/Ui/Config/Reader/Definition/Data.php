<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader\Definition;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\Config\Converter;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Ui\Config\Reader\Definition;
use Magento\Ui\Config\Reader\DefinitionFactory;

/**
 * Read UI Component definition configuration data ang evaluate arguments
 * @since 2.2.0
 */
class Data implements \Magento\Framework\Config\DataInterface
{
    /**
     * ID in the storage cache
     */
    const CACHE_ID = 'ui_component_configuration_definition_data';

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
     * Argument interpreter.
     *
     * @var InterpreterInterface
     * @since 2.2.0
     */
    private $argumentInterpreter;

    /**
     * @param DefinitionFactory $readerFactory
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param InterpreterInterface $argumentInterpreter
     * @since 2.2.0
     */
    public function __construct(
        DefinitionFactory $readerFactory,
        CacheInterface $cache,
        SerializerInterface $serializer,
        InterpreterInterface $argumentInterpreter
    ) {
        $this->readerFactory = $readerFactory;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->cacheId = static::CACHE_ID;
        $this->initData();
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
            /** @var Definition $reader */
            $reader = $this->readerFactory->create();
            $data = $reader->read();
            $this->cache->save($this->serializer->serialize($data), $this->cacheId);
        } else {
            $data = $this->serializer->unserialize($data);
        }

        if (!empty($data)) {
            $this->data = $this->evaluateComponentArguments($data);
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
        $this->data = array_replace_recursive($this->data, $config);
    }

    /**
     * Get config value by key
     *
     * @param string $key
     * @param mixed $default
     * @return array|mixed|null
     * @since 2.2.0
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * Evaluated components data
     *
     * @param array $components
     * @return array
     * @since 2.2.0
     */
    private function evaluateComponentArguments($components)
    {
        foreach ($components as &$component) {
            $component[Converter::DATA_ATTRIBUTES_KEY] = isset($component[Converter::DATA_ATTRIBUTES_KEY])
                ? $component[Converter::DATA_ATTRIBUTES_KEY]
                : [];
            $component[Converter::DATA_ARGUMENTS_KEY] = isset($component[Converter::DATA_ARGUMENTS_KEY])
                ? $component[Converter::DATA_ARGUMENTS_KEY]
                : [];

            foreach ($component[Converter::DATA_ARGUMENTS_KEY] as $argumentName => $argument) {
                $component[Converter::DATA_ARGUMENTS_KEY][$argumentName] =
                    $this->argumentInterpreter->evaluate($argument);
            }
        }

        return $components;
    }
}
