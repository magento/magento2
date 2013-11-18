<?php
/**
 * DataService invoker invokes the service, calls the methods and retrieves the data from the call.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService;

class Invoker
{
    /**
     * separates data structure hierarchy
     */
    const DATASERVICE_PATH_SEPARATOR = '.';

    /**
     * @var \Magento\Core\Model\DataService\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /** @var \Magento\Core\Model\DataService\Path\Composite */
    protected $_composite;

    /**
     * @var \Magento\Core\Model\DataService\Path\Navigator
     */
    private $_navigator;

    /**
     * @param \Magento\Core\Model\DataService\ConfigInterface $config
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\DataService\Path\Composite $composite
     * @param \Magento\Core\Model\DataService\Path\Navigator $navigator
     */
    public function __construct(
        \Magento\Core\Model\DataService\ConfigInterface $config,
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\DataService\Path\Composite $composite,
        \Magento\Core\Model\DataService\Path\Navigator $navigator
    ) {
        $this->_config = $config;
        $this->_objectManager = $objectManager;
        $this->_composite = $composite;
        $this->_navigator = $navigator;
    }

    /**
     * Call service method and retrieve the data (array) from the call
     *
     * @param string $sourceName
     * @throws \InvalidArgumentException
     * @return bool|array
     */
    public function getServiceData($sourceName)
    {
        $classInformation = $this->_config->getClassByAlias($sourceName);
        $instance = $this->_objectManager->get($classInformation['class']);
        $serviceData = $this->_applyMethod(
            $instance, $classInformation['retrieveMethod'],
            $classInformation['methodArguments']
        );
        if (!is_array($serviceData)) {
            $type = gettype($serviceData);
            throw new \InvalidArgumentException(
                "Data service method calls must return an array, received {$type} instead.
                 Called {$classInformation['class']}::{$classInformation['retrieveMethod']}"
            );
        }
        return $serviceData;
    }

    /**
     * Invoke method configured for service call
     *
     * @param Object $object
     * @param string $methodName
     * @param array $methodArguments
     * @return array
     */
    protected function _applyMethod($object, $methodName, $methodArguments)
    {
        $arguments = array();
        if (is_array($methodArguments)) {
            $arguments = $this->_prepareArguments($methodArguments);
        }
        return call_user_func_array(array($object, $methodName), $arguments);
    }

    /**
     * Prepare  values for the method params
     *
     * @param array $argumentsList
     * @return array
     */
    protected function _prepareArguments($argumentsList)
    {
        $result = array();
        foreach ($argumentsList as $name => $value) {
            $result[$name] = $this->getArgumentValue($value);
        }
        return $result;
    }

    /**
     * Get the value for the method argument
     *
     * @param string $valueTemplate
     * @return mixed
     */
    public function getArgumentValue($valueTemplate)
    {
        $composite = $this->_composite;
        $navigator = $this->_navigator;
        $callback = function ($matches) use ($composite, $navigator) {
            // convert from '{{parent.child}}' format to array('parent', 'child') format
            $pathArray = explode(\Magento\Core\Model\DataService\Invoker::DATASERVICE_PATH_SEPARATOR, $matches[1]);
            return $navigator->search($composite, $pathArray);
        };

        return preg_replace_callback('(\{\{(.*?)\}\})', $callback, $valueTemplate);
    }
}
