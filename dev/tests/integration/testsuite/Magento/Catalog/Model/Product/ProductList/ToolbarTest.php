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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
