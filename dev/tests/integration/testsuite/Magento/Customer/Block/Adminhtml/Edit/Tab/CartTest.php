<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    const CUSTOMER_ID_VALUE = 1234;

    /**
     * @var Context
     */
    private $_context;

    /**
     * @var Registry
     */
    private $_coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var Cart
     */
    private $_block;

    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManager::class);
        $this->_context = $this->_objectManager->get(
            \Magento\Backend\Block\Template\Context::class,
            ['storeManager' => $this->_storeManager]
        );

        $this->_coreRegistry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, self::CUSTOMER_ID_VALUE);

        $this->_block = $this->_objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart::class,
            '',
            ['context' => $this->_context, 'registry' => $this->_coreRegistry]
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Verify Grid with quote items
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_two_products_and_customer.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider getQuoteDataProvider
     *
     * @param int $customerId
     * @param bool $guest
     * @param bool $contains
     * @return void
     */
    public function testVerifyCollectionWithQuote(int $customerId, bool $guest, bool $contains): void
    {
        $session = $this->_objectManager->create(SessionQuote::class);
        $session->setCustomerId($customerId);
        $quoteFixture = $this->_objectManager->create(Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest($guest)
                     ->setCustomerId($customerId)
                     ->save();
        $this->_block->toHtml();
        if ($contains) {
            $this->assertStringContainsString(
                "We couldn&#039;t find any records",
                $this->_block->getGridParentHtml()
            );
        } else {
            $this->assertStringNotContainsString(
                "We couldn&#039;t find any records",
                $this->_block->getGridParentHtml()
            );
        }
    }

    /**
     * Data provider for withQuoteTest
     *
     * @return array
     */
    public function getQuoteDataProvider(): array
    {
        return [
            [
                 6,
                 false,
                 true
            ],
            [
                 self::CUSTOMER_ID_VALUE,
                 true,
                 false
            ],
        ];
    }

    /**
     * Verify Customer id
     *
     * @return void
     */
    public function testGetCustomerId(): void
    {
        $this->assertEquals(self::CUSTOMER_ID_VALUE, $this->_block->getCustomerId());
    }

    /**
     * Verify get grid url
     *
     * @return void
     */
    public function testGetGridUrl(): void
    {
        $this->assertStringContainsString('/backend/customer/index/cart', $this->_block->getGridUrl());
    }

    /**
     * Verify grid parent html
     *
     * @return void
     */
    public function testGetGridParentHtml(): void
    {
        $this->_block = $this->_objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart::class,
            '',
            []
        );
        $mockCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_block->setCollection($mockCollection);
        $this->assertStringContainsString(
            "<div class=\"admin__data-grid-header admin__data-grid-toolbar\"",
            $this->_block->getGridParentHtml()
        );
    }

    /**
     * Verify row url
     *
     * @return void
     */
    public function testGetRowUrl(): void
    {
        $row = new \Magento\Framework\DataObject();
        $row->setProductId(1);
        $this->assertStringContainsString('/backend/catalog/product/edit/id/1', $this->_block->getRowUrl($row));
    }

    /**
     * Verify get html
     *
     * @return void
     */
    public function testGetHtml(): void
    {
        $html = $this->_block->toHtml();
        $this->assertStringContainsString("<div id=\"customer_cart_grid\"", $html);
        $this->assertStringContainsString("<div class=\"admin__data-grid-header admin__data-grid-toolbar\"", $html);
        $this->assertStringContainsString("customer_cart_gridJsObject = new varienGrid(\"customer_cart_grid\",", $html);
        $this->assertStringContainsString(
            'backend\u002Fcustomer\u002Fcart_product_composite_cart\u002Fconfigure\u002Fcustomer_id\u002F'
            . self::CUSTOMER_ID_VALUE,
            $html
        );
    }
}
