<?php
/**
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Di\Child;

class Interceptor extends \Magento\Test\Di\Child
{
    /**
     * @var \Magento\Framework\ObjectManager\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_plugins = array();

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_pluginList = array();

    /**
     * @var string
     */
    protected $_subjectType;

    /**
     * @var array
     */
    protected $_arguments;

    /**
     * @param \Magento\Framework\ObjectManager\Factory $factory
     * @param \Magento\Framework\ObjectManager\ObjectManager $objectManager
     * @param string $subjectType
     * @param array $pluginList
     * @param array $arguments
     */
    public function __construct(
        \Magento\Framework\ObjectManager\Factory $factory,
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
