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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 *
 * @magentoAppArea adminhtml
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    const CUSTOMER_ID_VALUE = 1234;

    /** @var \Magento\Backend\Block\Template\Context */
    private $_context;

    /** @var \Magento\Framework\Registry */
    private $_coreRegistry;

    /** @var \Magento\Framework\StoreManagerInterface */
    private $_storeManager;

    /** @var Cart */
    private $_block;

    /** @var \Magento\Framework\ObjectManager */
    private $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManager');
        $this->_context = $this->_objectManager->get(
            'Magento\Backend\Block\Template\Context',
            array('storeManager' => $this->_storeManager)
        );

        $this->_coreRegistry = $this->_objectManager->get('Magento\Framework\Registry');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, self::CUSTOMER_ID_VALUE);

        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Cart',
            '',
            array('context' => $this->_context, 'registry' => $this->_coreRegistry)
        );
    }

    public function tearDown()
    {
        $this->_coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function testGetCustomerId()
    {
        $this->assertEquals(self::CUSTOMER_ID_VALUE, $this->_block->getCustomerId());
    }

    public function testGetGridUrl()
    {
        $this->assertContains('/backend/customer/index/cart', $this->_block->getGridUrl());
    }

    public function testGetGridParentHtml()
    {
        $this->_block = $this->_objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Cart',
            '',
            array()
        );
        $mockCollection = $this->getMockBuilder('\Magento\Framework\Data\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_block->setCollection($mockCollection);
        $this->assertContains("<div class=\"grid-actions\">", $this->_block->getGridParentHtml());
    }

    public function testGetRowUrl()
    {
        $row = new \Magento\Framework\Object();
        $row->setProductId(1);
        $this->assertContains('/backend/catalog/product/edit/id/1', $this->_block->getRowUrl($row));
    }

    public function testGetHtml()
    {
        $html = $this->_block->toHtml();
        $this->assertContains("<div id=\"customer_cart_grid\">", $html);
        $this->assertContains("<div class=\"grid-actions\">", $html);
        $this->assertContains("customer_cart_gridJsObject = new varienGrid('customer_cart_grid',", $html);
        $this->assertContains(
            "backend/customer/cart_product_composite_cart/configure/customer_id/" . self::CUSTOMER_ID_VALUE,
            $html
        );
    }
}
