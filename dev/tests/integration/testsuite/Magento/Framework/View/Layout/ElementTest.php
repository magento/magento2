<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $model;

    public function testPrepare()
    {
        /**
         * @TODO: Need to use ObjectManager instead 'new'.
         * On this moment we have next bug MAGETWO-4274 which blocker for this key.
         */
        $this->model = new \Magento\Framework\View\Layout\Element(__DIR__ . '/_files/_layout_update.xml', 0, true);

        list($blockNode) = $this->model->xpath('//block[@name="nodeForTesting"]');
        list($actionNode) = $this->model->xpath('//action[@method="setSomething"]');

        $this->assertEmpty($blockNode->attributes()->parent);
        $this->assertEmpty($actionNode->attributes()->block);

        $this->model->prepare();

        $this->assertEquals('root', (string)$blockNode->attributes()->parent);
        $this->assertEquals('Magento\Backend\Block\Page', (string)$blockNode->attributes()->class);
        $this->assertEquals('nodeForTesting', (string)$actionNode->attributes()->block);
    }
}
