<?php
/**
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
namespace Magento\Framework\Interception\Chain;

use Magento\Framework\Code\GeneratorTest\SourceClassWithNamespace\Interceptor;
use Magento\Framework\Interception\Definition;
use Magento\Framework\Interception\PluginList;

class Chain implements \Magento\Framework\Interception\Chain
{
    /**
     * @var \Magento\Framework\Interception\PluginList
     */
    protected $pluginList;

    /**
     * @param PluginList $pluginList
     */
    public function __construct(PluginList $pluginList)
    {
        $this->pluginList = $pluginList;
    }

    /**
     * Invoke next plugin in chain
     *
     * @param string $type
     * @param string $method
     * @param string $previousPluginCode
     * @param Interceptor $subject
     * @param array $arguments
     * @return mixed|void
     */
    public function invokeNext($type, $method, $subject, array $arguments, $previousPluginCode = null)
    {
        $pluginInfo = $this->pluginList->getNext($type, $method, $previousPluginCode);
        $capMethod = ucfirst($method);
        $result = null;
        if (isset($pluginInfo[Definition::LISTENER_BEFORE])) {
            foreach ($pluginInfo[Definition::LISTENER_BEFORE] as $code) {
                $beforeResult = call_user_func_array(
                    array($this->pluginList->getPlugin($type, $code), 'before' . $capMethod),
                    array_merge(array($subject), $arguments)
                );
                if ($beforeResult) {
                    $arguments = $beforeResult;
                }
            }
        }
        if (isset($pluginInfo[Definition::LISTENER_AROUND])) {
            $chain = $this;
            $code = $pluginInfo[Definition::LISTENER_AROUND];
            $next = function () use ($chain, $type, $method, $subject, $code) {
                return $chain->invokeNext($type, $method, $subject, func_get_args(), $code);
            };
            $result = call_user_func_array(
                array($this->pluginList->getPlugin($type, $code), 'around' . $capMethod),
                array_merge(array($subject, $next), $arguments)
            );
        } else {
            $result = $subject->___callParent($method, $arguments);
        }
        if (isset($pluginInfo[Definition::LISTENER_AFTER])) {
            foreach ($pluginInfo[Definition::LISTENER_AFTER] as $code) {
                $result = $this->pluginList->getPlugin($type, $code)->{'after' . $capMethod}($subject, $result);
            }
        }
        return $result;
    }
}
