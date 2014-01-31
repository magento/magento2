<?php
/**
 * Scope Reader
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App\Config\Scope;

class Reader
{
    /**
     * @var \Magento\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\App\Config\ScopePool
     */
    protected $_sectionPool;

    /**
     * @var \Magento\Config\ConverterInterface
     */
    protected $_converter;

    /**
     * @var \Magento\App\Config\Data\ProcessorInterface
     */
    protected $_processor;

    /**
     * @var \Magento\App\Config\Scope\FactoryInterface
     */
    protected $_scopeFactory;

    /**
     * @var \Magento\App\Config\Scope\HierarchyInterface
     */
    protected $_scopeHierarchy;

    /**
     *
     * @param \Magento\App\Config\Initial $initialConfig
     * @param \Magento\Config\ConverterInterface $converter
     * @param \Magento\App\Config\Data\ProcessorInterface $processor
     * @param \Magento\App\Config\Scope\FactoryInterface $scopeFactory
     * @param \Magento\App\Config\Scope\HierarchyInterface $scopeHierarchy
     */
    public function __construct(
        \Magento\App\Config\Initial $initialConfig,
        \Magento\Config\ConverterInterface $converter,
        \Magento\App\Config\Data\ProcessorInterface $processor,
        \Magento\App\Config\Scope\FactoryInterface $scopeFactory,
        \Magento\App\Config\Scope\HierarchyInterface $scopeHierarchy
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_converter = $converter;
        $this->_processor = $processor;
        $this->_scopeFactory = $scopeFactory;
        $this->_scopeHierarchy = $scopeHierarchy;
    }

    public function read($scope)
    {
        $config = array();
        $scopes = $this->_scopeHierarchy->getHierarchy($scope);
        foreach ($scopes as $scope) {
            $config = array_replace_recursive($config, $this->_getInitialConfigData($scope));
            $config = array_replace_recursive($config, $this->_getExtendedConfigData($scope));
        }
        return $this->_processor->processValue($config);
    }

    /**
     * Retrieve initial scope config from xml files
     *
     * @param string $scope
     * @return array
     */
    protected function _getInitialConfigData($scope)
    {
        return $this->_initialConfig->getData($scope);
    }

    /**
     * Retrieve scope config from database
     *
     * @param string $scope
     * @return array
     */
    protected function _getExtendedConfigData($scope)
    {
        list($scopeType, $scopeCode) = array_pad(explode('|', $scope), 2, null);
        if (null === $scopeCode) {
            $collection = $this->_scopeFactory->create(array('scope' => $scopeType));
        } else {
            $collection = $this->_scopeFactory->create(array('scope' => $scopeType, 'scopeId' => $scopeCode));
        }

        $config = array();
        foreach ($collection as $item) {
            $config[$item->getPath()] = $item->getValue();
        }
        return $this->_converter->convert($config);
    }
}
