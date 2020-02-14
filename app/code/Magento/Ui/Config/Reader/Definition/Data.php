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
 */
class Data implements \Magento\Framework\Config\DataInterface
{
    const CACHE_ID = 'ui_component_configuration_definition_data';

    const SEARCH_PATTERN = '%s.xml';

    /**
     * Config data
     *
     * @var array
     */
    private $data = [];

    /**
     * @var ReaderFactory
     */
    private $readerFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Argument interpreter.
     *
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    /**
     * @param DefinitionFactory $readerFactory
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param InterpreterInterface $argumentInterpreter
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
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Evaluated components data
     *
     * @param array $components
     * @return array
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
