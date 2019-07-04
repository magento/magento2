<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Model\Indexer\Category\Product\Action\Full as OriginObject;
use Magento\TestFramework\Catalog\Model\Indexer\Category\Product\Action\Full as PreferenceObject;
use Magento\Framework\Interception\PluginListInterface;

/**
 * Class FullTest
 * @package Magento\Catalog\Model\Indexer\Category\Product\Action
 */
class FullTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PreferenceObject
     */
    private $interceptor;

    /**
     * List of plugins
     *
     * @var PluginListInterface
     */
    private $pluginList;

    /**
     * Prepare data for test
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->configure(['preferences' => [OriginObject::class => PreferenceObject::class]]);
        $this->interceptor = $objectManager->get(OriginObject::class);
        $this->pluginList = $objectManager->get(PluginListInterface::class);
    }

    /**
     * Test possibility to add object preference
     */
    public function testPreference()
    {
        $interceptorClassName = get_class($this->interceptor);

        // Check interceptor class name
        $this->assertEquals($interceptorClassName, PreferenceObject::class . '\Interceptor');

        //check that there are no fatal errors
        $this->pluginList->getNext($interceptorClassName, 'execute');
    }
}
