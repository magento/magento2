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
            array('coreRegistry' => $this->coreRegistry)
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
        $this->assertEquals('Firstname Lastname', $this->block->getHeaderText());
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
