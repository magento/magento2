<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Address;

use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests Address Edit Block
 *
 * @see \Magento\Customer\Block\Address\Edit
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 *
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class EditTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Edit */
    private $block;

    /** @var  Session */
    private $customerSession;

    /** @var AddressRegistry */
    private $addressRegistry;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var RequestInterface */
    private $request;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->customerSession->setCustomerId(1);
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->request->setParam('id', '1');
        /** @var Page $page */
        $page = $this->objectManager->get(PageFactory::class)->create();
        $page->addHandle(['default', 'customer_address_form']);
        $page->getLayout()->generateXml();
        $this->block = $page->getLayout()->getBlock('customer_address_edit');
        $this->addressRegistry = $this->objectManager->get(AddressRegistry::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->customerSession->setCustomerId(null);
        $this->request->setParam('id', null);
        //Cleanup address from registry
        $this->addressRegistry->remove(1);
        $this->addressRegistry->remove(2);
        //Cleanup customer from registry
        $this->customerRegistry->remove(1);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testGetSaveUrl(): void
    {
        $this->assertEquals('http://localhost/index.php/customer/address/formPost/', $this->block->getSaveUrl());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testGetRegionId(): void
    {
        $this->assertEquals(1, $this->block->getRegionId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testGetCountryId(): void
    {
        $this->assertEquals('US', $this->block->getCountryId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @return void
     */
    public function testGetCustomerAddressCount(): void
    {
        $this->assertEquals(2, $this->block->getCustomerAddressCount());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testCanSetAsDefaultShipping(): void
    {
        $this->assertEquals(0, $this->block->canSetAsDefaultShipping());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testIsDefaultBilling(): void
    {
        $this->assertFalse($this->block->isDefaultBilling());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testGetStreetLine(): void
    {
        $this->assertEquals('Green str, 67', $this->block->getStreetLine(1));
        $this->assertEquals('', $this->block->getStreetLine(2));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/create_account/vat_frontend_visibility 1
     * @return void
     */
    public function testVatIdFieldVisible(): void
    {
        $html = $this->block->toHtml();
        $labelXpath = "//div[contains(@class, 'taxvat')]//label/span[normalize-space(text()) = '%s']";
        $this->assertEquals(1, Xpath::getElementsCountForXpath(sprintf($labelXpath, __('VAT Number')), $html));
        $inputXpath = "//div[contains(@class, 'taxvat')]//div/input[contains(@id,'vat_id') and @type='text']";
        $this->assertEquals(1, Xpath::getElementsCountForXpath($inputXpath, $html));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store customer/create_account/vat_frontend_visibility 0
     * @return void
     */
    public function testVatIdFieldNotVisible(): void
    {
        $html = $this->block->toHtml();
        $labelXpath = "//div[contains(@class, 'taxvat')]//label/span[normalize-space(text()) = '%s']";
        $this->assertEquals(0, Xpath::getElementsCountForXpath(sprintf($labelXpath, __('VAT Number')), $html));
        $inputXpath = "//div[contains(@class, 'taxvat')]//div/input[contains(@id,'vat_id') and @type='text']";
        $this->assertEquals(0, Xpath::getElementsCountForXpath($inputXpath, $html));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/attribute_postcode_store_label_address.php
     *
     * @return void
     */
    public function testCheckPostCodeLabels(): void
    {
        $html = $this->executeInStoreContext->execute('default', [$this->block, 'toHtml']);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//form[contains(@class, 'form-address-edit')]//label[@for='zip']/span[contains(text(), '%s')]",
                    'default store postcode label'
                ),
                $html
            )
        );
    }

    /**
     * Check that submit button is disabled
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testSubmitButtonIsDisabled(): void
    {
        $html = $this->block->toHtml();
        $buttonXpath = "//form[contains(@class, 'form-address-edit')]//button[@type='submit' and @disabled='disabled']";
        $this->assertEquals(1, Xpath::getElementsCountForXpath($buttonXpath, $html));
    }
}
