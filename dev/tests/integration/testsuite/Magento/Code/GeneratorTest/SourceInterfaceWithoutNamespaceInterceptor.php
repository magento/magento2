<?php
/**
 * Interceptor class for \Magento\Code\GeneratorTest\SourceInterfaceWithoutNamespace
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Code\GeneratorTest;

class SourceInterfaceWithoutNamespaceInterceptor
    implements \Magento\Code\GeneratorTest\SourceInterfaceWithoutNamespace
{
    /**
     * Object Manager factory
     *
     * @var \Magento\ObjectManager\Factory
     */
    protected $_factory = null;

    /**
     * Object Manager instance
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * Subject type
     *
     * @var string
     */
    protected $_subjectType = null;

    /**
     * Subject
     *
     * @var \Magento\Code\GeneratorTest\SourceInterfaceWithoutNamespace
     */
    protected $_subject = null;

    /**
     * List of plugins
     *
     * @var \Magento\Interception\PluginList
     */
    protected $_pluginList = null;

    /**
     * Subject constructor arguments
     *
     * @var array
     */
    protected $_arguments = null;

    /**
     * Interceptor constructor
     *
     * @param \Magento\ObjectManager\Factory $factory
     * @param \Magento\ObjectManager\ObjectManager $objectManager
     * @param string $subjectType
     * @param \Magento\Interception\PluginList $pluginList
     * @param array $arguments
     */
    public function __construct(
        \Magento\ObjectManager\Factory $factory,
        \Magento\ObjectManager\ObjectManager $objectManager,
        $subjectType,
        \Magento\Interception\PluginList $pluginList,
        array $arguments
    ) {
        $this->_factory = $factory;
        $this->_objectManager = $objectManager;
        $this->_subjectType = $subjectType;
        $this->_pluginList = $pluginList;
        $this->_arguments = $arguments;
    }

    /**
     * Retrieve subject
     *
     * @return mixed
     */
    protected function _getSubject()
    {
        if (is_null($this->_subject)) {
            $this->_subject = $this->_factory->create($this->_subjectType, $this->_arguments);
        }
        return $this->_subject;
    }

    /**
     * Invoke method
     *
     * @param string $methodName
     * @param array $methodArguments
     * @return mixed
     */
    protected function _invoke($methodName, array $methodArguments)
    {
        $beforeMethodName = 'before' . $methodName;
        foreach ($this->_pluginList->getPlugins($this->_subjectType, $methodName, 'before') as $plugin) {
            $methodArguments = $this->_objectManager->get($plugin)
                ->$beforeMethodName($methodArguments);
        }
        $invocationChain = new \Magento\Code\Plugin\InvocationChain(
            $this->_getSubject(),
            $methodName,
            $this->_objectManager,
            $this->_pluginList->getPlugins($this->_subjectType, $methodName, 'around')
        );
        $invocationResult = $invocationChain->proceed($methodArguments);
        $afterMethodName = 'after' . $methodName;
        $afterPlugins = array_reverse(
            $this->_pluginList->getPlugins($this->_subjectType, $methodName, 'after')
        );
        foreach ($afterPlugins as $plugin) {
            $invocationResult = $this->_objectManager->get($plugin)
                ->$afterMethodName($invocationResult);
        }
        return $invocationResult;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $this->_getSubject();
        return array('_subject', '_subjectType');
    }

    /**
     * Clone subject instance
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Retrieve ObjectManager from the global scope
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Core\Model\ObjectManager::getInstance();
        $this->_pluginList = $this->_objectManager->get('Magento\Interception\PluginList');
    }

    /**
     * {@inheritdoc}
     */
    public function doWorkWithParams($param1, array $param2)
    {
        return $this->_invoke('doWorkWithParams', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function doWork()
    {
        return $this->_invoke('doWork', func_get_args());
    }
}
