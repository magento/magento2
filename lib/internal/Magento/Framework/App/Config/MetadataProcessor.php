<?php
/**
 * Configuration metadata processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Class \Magento\Framework\App\Config\MetadataProcessor
 *
 * @since 2.0.0
 */
class MetadataProcessor
{
    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorFactory
     * @since 2.0.0
     */
    protected $_processorFactory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_metadata = [];

    /**
     * @param \Magento\Framework\App\Config\Data\ProcessorFactory $processorFactory
     * @param Initial $initialConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\Data\ProcessorFactory $processorFactory,
        Initial $initialConfig
    ) {
        $this->_processorFactory = $processorFactory;
        $this->_metadata = $initialConfig->getMetadata();
    }

    /**
     * Retrieve array value by path
     *
     * @param array $data
     * @param string $path
     * @return string|null
     * @since 2.0.0
     */
    protected function _getValue(array $data, $path)
    {
        $keys = explode('/', $path);
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }

    /**
     * Set array value by path
     *
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    protected function _setValue(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $currentPointer = & $container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = [];
            }
            $currentPointer = & $currentPointer[$segment];
        }
        $currentPointer = $value;
    }

    /**
     * Process config data
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    public function process(array $data)
    {
        foreach ($this->_metadata as $path => $metadata) {
            /** @var \Magento\Framework\App\Config\Data\ProcessorInterface $processor */
            $processor = $this->_processorFactory->get($metadata['backendModel']);
            $value = $processor->processValue($this->_getValue($data, $path));
            $this->_setValue($data, $path, $value);
        }
        return $data;
    }
}
