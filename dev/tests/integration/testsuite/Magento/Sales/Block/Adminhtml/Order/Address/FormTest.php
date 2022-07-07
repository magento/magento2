<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Address;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\App\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Checks order address edit form block
 *
 * @see \Magento\Sales\Block\Adminhtml\Order\Address\Form
 *
 * @magentoAppArea adminhtml
 */
class FormTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Form */
    private $block;

    /** @var Registry */
    private $registry;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var CollectionFactory */
    private $countryCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Form::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->objectManager->removeSharedInstance(Config::class);
        $this->countryCollectionFactory = $this->objectManager->get(CollectionFactory::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('order_address');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testGetFormValues(): void
    {
        $address = $this->getOrderAddress('100000001');
        $this->registerOrderAddress($address);
        $formValues = $this->block->getFormValues();
        $this->assertEquals($address->getData(), $formValues);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoConfigFixture default_store general/country/default US
     * @magentoConfigFixture default_store general/country/allow US
     * @magentoConfigFixture fixture_second_store_store general/country/default UY
     * @magentoConfigFixture fixture_second_store_store general/country/allow UY
     * @return void
     */
    public function testCountryIdInAllowedList(): void
    {
        $address = $this->getOrderAddress('100000001');
        $this->registerOrderAddress($address);
        $this->assertEquals('US', $address->getCountryId());
        $this->assertCountryField('US');
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoConfigFixture default_store general/country/default CA
     * @magentoConfigFixture default_store general/country/allow CA
     * @magentoConfigFixture fixture_second_store_store general/country/default UY
     * @magentoConfigFixture fixture_second_store_store general/country/allow UY
     * @return void
     */
    public function testCountryIdInNotAllowedList(): void
    {
        $address = $this->getOrderAddress('100000001');
        $this->registerOrderAddress($address);
        $this->assertCountryField('CA');
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer_on_second_website.php
     *
     * @magentoConfigFixture default_store general/country/default UA
     * @magentoConfigFixture default_store general/country/allow UA
     *
     * @return void
     */
    public function testFormRenderedWithSelectRegionInput(): void
    {
        $address = $this->getOrderAddress('100000001');
        $this->registerOrderAddress($address);
        $form = $this->block->getForm();
        $countryElement = $form->getElement('country_id');
        $this->assertNotNull($countryElement);
        $this->assertEquals('US', $countryElement->getEscapedValue());
        $html = $form->toHtml();
        $regionIdSelectXpath = '//select[@id=\'region_id\']';
        $this->assertEquals(1, Xpath::getElementsCountForXpath($regionIdSelectXpath, $html));
        $countryOptionsXpath = '//select[@id=\'country_id\']/option';
        $allowedCountriesCount = count($this->countryCollectionFactory->create()->loadByStore());
        $this->assertEquals($allowedCountriesCount, Xpath::getElementsCountForXpath($countryOptionsXpath, $html));
    }

    /**
     * Prepares address edit from block.
     *
     * @param OrderAddressInterface $address
     * @return void
     */
    private function registerOrderAddress(OrderAddressInterface $address): void
    {
        $this->registry->unregister('order_address');
        $this->registry->register('order_address', $address);
    }

    /**
     * Return order billing address.
     *
     * @param string $orderIncrementId
     * @return OrderAddressInterface
     */
    private function getOrderAddress(string $orderIncrementId): OrderAddressInterface
    {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);

        return $order->getBillingAddress();
    }

    /**
     * Asserts country field data.
     *
     * @param string $countryCode
     * @return void
     */
    private function assertCountryField(string $countryCode): void
    {
        $countryIdField = $this->block->getForm()->getElement('country_id');
        $this->assertEquals($countryCode, $countryIdField->getValue());
        $options = $countryIdField->getValues();
        $this->assertCount(1, $options);
        $firstOption = reset($options);
        $this->assertEquals($countryCode, $firstOption['value']);
    }
}
