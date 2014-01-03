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
 * @category    Magento
 * @package     Magento_Code
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Code\Plugin;

class InvocationChain
{
    /**
     * Original instance whose behavior is decorated by plugins
     *
     * @var mixed
     */
    protected $_subject;

    /**
     * Name of the method to invoke
     *
     * @var string
     */
    protected $_methodName;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * List of the plugins
     *
     * @var array
     */
    protected $_pluginList;

    /**
     * @param mixed $subject
     * @param string $methodName
     * @param \Magento\ObjectManager $objectManager
     * @param array $pluginList
     */
    public function __construct($subject, $methodName, \Magento\ObjectManager $objectManager, array $pluginList)
    {
        $this->_subject = $subject;
        $this->_methodName = $methodName;
        $this->_objectManager = $objectManager;
        $this->_pluginList = $pluginList;
    }

    /**
     * Propagate invocation through the chain
     *
     * @param array $arguments
     * @return mixed
     */
    public function proceed(array $arguments)
    {
        if (count($this->_pluginList)) {
            $aroundMethodName = 'around' . ucfirst($this->_methodName);
            return $this->_objectManager->get(array_shift($this->_pluginList))->$aroundMethodName($arguments, $this);
        }
        return call_user_func_array(array($this->_subject, $this->_methodName), $arguments);
    }
}
