<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\ProductList;

/**
 * Test \Magento\Catalog\Model\Product\ProductList\Toolbar
 */
class ToolbarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar
     */
    protected $toolbar;

    protected function setUp()
    {
        $this->toolbar = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product\ProductList\Toolbar'
        );
        $_COOKIE = [];
    }

    public function tearDown()
    {
        $_COOKIE = [];
    }

    public function testGetOrder()
    {
        $this->assertNull($this->toolbar->getOrder());
        $_COOKIE[Toolbar::ORDER_COOKIE_NAME] = 'orderCookie';
        $this->assertEquals('orderCookie', $this->toolbar->getOrder());
    }

    public function testGetDirection()
    {
        $this->assertNull($this->toolbar->getDirection());
        $_COOKIE[Toolbar::DIRECTION_COOKIE_NAME] = 'directionCookie';
        $this->assertEquals('directionCookie', $this->toolbar->getDirection());
    }

    public function testGetMode()
    {
        $this->assertNull($this->toolbar->getMode());
        $_COOKIE[Toolbar::MODE_COOKIE_NAME] = 'modeCookie';
        $this->assertEquals('modeCookie', $this->toolbar->getMode());
    }

    public function testGetLimit()
    {
        $this->assertNull($this->toolbar->getLimit());
        $_COOKIE[Toolbar::LIMIT_COOKIE_NAME] = 'limitCookie';
        $this->assertEquals('limitCookie', $this->toolbar->getLimit());
    }
}
