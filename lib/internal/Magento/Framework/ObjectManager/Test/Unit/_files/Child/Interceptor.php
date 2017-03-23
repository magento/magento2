<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Child;

class Interceptor extends \Magento\Test\Di\Child
{
    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_plugins = [];

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_pluginList = [];

    /**
     * @var string
     */
    protected $_subjectType;

    /**
     * @var array
     */
    protected $_arguments;

    /**
     * @param \Magento\Framework\ObjectManager\FactoryInterface $factory
     * @param \Magento\Framework\ObjectManager\ObjectManager $objectManager
     * @param string $subjectType
     * @param array $pluginList
     * @param array $arguments
     */
    public function __construct(
        \Magento\Framework\ObjectManager\FactoryInterface $factory,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        $subjectType,
        array $pluginList,
        array $arguments
    ) {
        $this->_factory = $factory;
        $this->_pluginList = $pluginList;
        $this->_objectManager = $objectManager;
        $this->_subjectType = $subjectType;
        $this->_arguments = $arguments;
    }

    /**
     * @return object
     */
    protected function _getSubject()
    {
        return $this->_factory->create($this->_subjectType, $this->_arguments);
    }

    /**
     * @param string $param
     * @return mixed
     */
    public function wrap($param)
    {
        $beforeFunc = __FUNCTION__ . 'Before';
        if (isset($this->_pluginList[$beforeFunc])) {
            foreach ($this->_pluginList[$beforeFunc] as $plugin) {
                $param = $this->_objectManager->get($plugin)->{$beforeFunc}($param);
            }
        }
        $insteadFunc = __FUNCTION__;
        if (isset($this->_pluginList[$insteadFunc])) {
            $first = reset($this->_pluginList[$insteadFunc]);
            $returnValue = $this->_objectManager->get($first)->{$insteadFunc}();
        } else {
            $returnValue = $this->_getSubject()->wrap($param);
        }
        $afterFunc = __FUNCTION__ . 'After';
        if (isset($this->_pluginList[$afterFunc])) {
            foreach (array_reverse($this->_pluginList[$afterFunc]) as $plugin) {
                $returnValue = $this->_objectManager->get($plugin)->{$afterFunc}($returnValue);
            }
        }
        return $returnValue;
    }
}
