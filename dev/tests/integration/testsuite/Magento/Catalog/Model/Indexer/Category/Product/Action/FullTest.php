<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Model\Indexer\Category\Product\Action\Full as OriginObject;
use Magento\TestFramework\Catalog\Model\Indexer\Category\Product\Action\Full as PreferenceObject;
use Magento\Framework\Interception\PluginListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Test for Magento\Catalog\Model\Indexer\Category\Product\Action\Full *
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
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $preferenceObject = $this->objectManager->get(PreferenceObject::class);
        $this->objectManager->addSharedInstance($preferenceObject, OriginObject::class);
        $this->interceptor = $this->objectManager->get(OriginObject::class);
        $this->pluginList = $this->objectManager->get(PluginListInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(OriginObject::class);
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
