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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class CartTest
 *
 * @magentoAppArea adminhtml
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Shopping cart.
     *
     * @var Cart
     */
    private $block;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get('Magento\Framework\Registry');
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);

        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View\Cart',
            '',
            array('coreRegistry' => $this->coreRegistry, 'data' => array('website_id' => 1))
        );
        $this->block->getPreparedCollection();
    }

    /**
     * Execute per test cleanup.
     */
    public function tearDown()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Verify that the Url for a product row in the cart grid is correct.
     */
    public function testGetRowUrl()
    {
        $row = new \Magento\Framework\Object(array('product_id' => 1));
        $this->assertContains('catalog/product/edit/id/1', $this->block->getRowUrl($row));
    }

    /**
     * Verify that the headers in the cart grid are visible.
     */
    public function testGetHeadersVisibility()
    {
        $this->assertTrue($this->block->getHeadersVisibility());
    }

    /**
     * Verify that the customer has a single item in his cart.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     */
    public function testGetCollection()
    {
        $this->assertEquals(1, $this->block->getCollection()->getSize());
    }

    /**
     * Verify the basic content of an empty cart.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testToHtmlEmptyCart()
    {
        $this->assertEquals(0, $this->block->getCollection()->getSize());
        $this->assertContains("There are no items in customer's shopping cart at the moment", $this->block->toHtml());
    }

    /**
     * Verify the Html content for a single item in the customer's cart.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     */
    public function testToHtmlCartItem()
    {
        $html = $this->block->toHtml();
        $this->assertContains('Simple Product', $html);
        $this->assertContains('simple', $html);
        $this->assertContains('$10.00', $html);
        $this->assertContains('catalog/product/edit/id/1', $html);
    }
}
