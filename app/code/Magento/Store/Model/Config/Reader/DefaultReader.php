<?php
/**
 * Default configuration reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader;

class DefaultReader implements \Magento\Framework\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\Framework\App\Config\Scope\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Store\Model\Resource\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\Config\Scope\Converter $converter
     * @param \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\Config\Scope\Converter $converter,
        \Magento\Store\Model\Resource\Config\Collection\ScopedFactory $collectionFactory
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Read configuration data
     *
     * @return array
     */
    public function read()
    {
        $config = $this->_initialConfig->getData(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT);

        $collection = $this->_collectionFactory->create(
            ['scope' => \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT]
        );
        $dbDefaultConfig = [];
        foreach ($collection as $item) {
            $dbDefaultConfig[$item->getPath()] = $item->getValue();
        }
        $dbDefaultConfig = $this->_converter->convert($dbDefaultConfig);
        $config = array_replace_recursive($config, $dbDefaultConfig);

        return $config;
    }
}
