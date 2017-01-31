<?php
/**
 * Configuration metadata processor
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class MetadataProcessor
{
    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorFactory
     */
    protected $_processorFactory;

    /**
     * @var array
     */
    protected $_metadata = [];

    /**
     * @param \Magento\Framework\App\Config\Data\ProcessorFactory $processorFactory
     * @param Initial $initialConfig
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
