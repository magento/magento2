<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Interception\Sample;

use Magento\Framework\Interception;
use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\Interception\PluginListInterface;

/**
 * Sample interceptor
 */
class Interceptor extends Entity implements InterceptorInterface
{
    use Interception\Interceptor;

    public function __construct()
    {
        $this->___init();
    }

    /**
     * {@inheritdoc}
     */
    public function ___init()
    {
        $this->subjectType = get_parent_class($this);
    }

    /**
     * {@inheritdoc}
     */
    public function doSomething()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'doSomething');
        if (!$pluginInfo) {
            return parent::doSomething();
        } else {
            return $this->___callPlugins('doSomething', func_get_args(), $pluginInfo);
        }
    }

    /**
     * Set plugin list
     *
     * @param Interception\PluginListInterface $pluginList
     * @return void
     */
    public function setPluginList(PluginListInterface $pluginList)
    {
        $this->pluginList = $pluginList;
    }
}
