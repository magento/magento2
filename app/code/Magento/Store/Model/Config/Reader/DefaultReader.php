<?php
/**
 * Default configuration reader
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @var \Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\Config\Scope\Converter $converter
     * @param \Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\Config\Scope\Converter $converter,
        \Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory $collectionFactory
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Read configuration data
     *
     * @param null|string $scope
     * @throws LocalizedException Exception is thrown when scope other than default is given
     * @return array
     */
    public function read($scope = null)
    {
        $scope = $scope === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : $scope;
        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Only default scope allowed"));
        }

        $config = $this->_initialConfig->getData($scope);

        $collection = $this->_collectionFactory->create(
            ['scope' => $scope]
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
