<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class EditTest
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The edit block under test.
     *
     * @var Edit
     */
    private $block;

    /**
     * Core Registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * The customer Id.
     *
     * @var int
     */
    private static $customerId = 1;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get('Magento\Framework\Registry');
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, self::$customerId);

        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit',
            '',
            ['coreRegistry' => $this->coreRegistry]
        );
    }

    /**
     * Execute post class cleanup after all tests have executed.
     */
    public function tearDown()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Verify that the customer Id is the one that was set in the registry.
     */
    public function testGetCustomerId()
    {
        $this->assertEquals(self::$customerId, $this->block->getCustomerId());
    }

    /**
     * Verify that the correct order create Url is generated.
     */
    public function testGetCreateOrderUrl()
    {
        $this->assertContains(
            'sales/order_create/start/customer_id/' . self::$customerId,
            $this->block->getCreateOrderUrl()
        );
    }

    /**
     * Verify that the header text is correct for a new customer.
     */
    public function testGetHeaderTextNewCustomer()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->assertEquals('New Customer', $this->block->getHeaderText());
    }

    /**
     * Verify that the header text is correct for an existing customer.
     */
    public function testGetHeaderTextExistingCustomer()
    {
        $this->assertEquals('John Smith', $this->block->getHeaderText());
    }

    /**
     * Verify that the correct customer validation Url is generated.
     */
    public function testGetValidationUrl()
    {
        $this->assertContains('customer/index/validate', $this->block->getValidationUrl());
    }

    /**
     * Verify the basic content of the block's form Html.
     */
    public function testGetFormHtml()
    {
        $html = $this->block->getFormHtml();
        $this->assertContains('<div class="entry-edit form-inline">', $html);
        $this->assertStringMatchesFormat('%a name="customer_id" %s value="' . self::$customerId . '" %a', $html);
        $this->assertContains('id="product_composite_configure_form"', $html);
    }
}
