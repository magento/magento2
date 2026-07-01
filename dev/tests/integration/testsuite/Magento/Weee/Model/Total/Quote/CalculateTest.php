<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Model\Total\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Multishipping\Test\Fixture\AddAddressToCart as AddAddressToCartFixture;
use Magento\Multishipping\Test\Fixture\ShippingAssignments as ShippingAssignmentsFixture;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Test\Fixture\Attribute as FptAttributeFixture;
use PHPUnit\Framework\TestCase;

/**
 * Quote totals calculate tests class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CalculateTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TotalsInformationManagement
     */
    private $totalsManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var WeeeHelper
     */
    private $weeeHelper;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->totalsManagement = $this->objectManager->get(TotalsInformationManagement::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->weeeHelper = $this->objectManager->get(WeeeHelper::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Multishipping quote with FPT Weee TAX totals calculation test
     *
     * @magentoDataFixture Magento/Weee/_files/quote_multishipping.php
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/apply_vat 1
     */
    public function testGetWeeTaxTotals()
    {
        /** @var QuoteFactory $quoteFactory */
        $quoteFactory = $this->objectManager->get(QuoteFactory::class);
        /** @var QuoteResource $quoteResource */
        $quoteResource = $this->objectManager->get(QuoteResource::class);
        $quote = $quoteFactory->create();
        $quoteResource->load($quote, 'multishipping_fpt_quote_id', 'reserved_order_id');
        $cartId = $quote->getId();

        $actual = $this->getTotals((int) $cartId);

        $items = $actual->getTotalSegments();
        $this->assertTrue(array_key_exists('weee_tax', $items));
        $this->assertEquals(25.4, $items['weee_tax']->getValue());
    }

    #[
        Config('tax/weee/enable', '1', 'store', 'default'),
        Config('tax/weee/apply_vat', '1', 'store', 'default'),
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr'], 'fpt'),
        DataFixture(
            ProductFixture::class,
            ['fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.3]]],
            'p1'
        ),
        DataFixture(
            ProductFixture::class,
            ['fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.8]]],
            'p2'
        ),
        DataFixture(GuestCartFixture::class, as: 'qt'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$qt.id$', 'product_id' => '$p1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$qt.id$', 'product_id' => '$p2.id$']),
    ]
    public function testCollectTotalsWithMultipleProducts(): void
    {
        $json = $this->objectManager->get(Json::class);
        $cart = $this->fixtures->get('qt');
        $totals = $this->getTotals((int) $cart->getId());
        $this->assertCount(2, $totals->getItems());
        $items = array_values($totals->getItems());

        $weeeTaxes = $json->unserialize($items[0]->getWeeeTaxApplied());
        $this->assertCount(1, $weeeTaxes);
        $this->assertEquals(0.3, $weeeTaxes[0]['base_amount_incl_tax']);
        $this->assertEquals(0.3, $weeeTaxes[0]['row_amount_incl_tax']);

        $weeeTaxes = $json->unserialize($items[1]->getWeeeTaxApplied());
        $this->assertCount(1, $weeeTaxes);
        $this->assertEquals(0.8, $weeeTaxes[0]['base_amount_incl_tax']);
        $this->assertEquals(0.8, $weeeTaxes[0]['row_amount_incl_tax']);
    }

    #[
        Config('tax/weee/enable', '1', 'store', 'default'),
        Config('tax/weee/apply_vat', '1', 'store', 'default'),
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr'], 'fpt'),
        DataFixture(
            ProductFixture::class,
            ['fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.3]]],
            'p1'
        ),
        DataFixture(
            ProductFixture::class,
            ['fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.8]]],
            'p2'
        ),
        DataFixture(GuestCartFixture::class, as: 'qt'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$qt.id$', 'product_id' => '$p1.id$'], 'qti1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$qt.id$', 'product_id' => '$p2.id$'], 'qti2'),
        DataFixture(AddAddressToCartFixture::class, ['cart_id' => '$qt.id$'], 'qta1'),
        DataFixture(AddAddressToCartFixture::class, ['cart_id' => '$qt.id$'], 'qta2'),
        DataFixture(
            ShippingAssignmentsFixture::class,
            [
                'cart_id' => '$qt.id$',
                'assignments' => [
                    ['item_id' => '$qti1.id$', 'address_id' => '$qta1.id$', 'qty' => 1],
                    ['item_id' => '$qti2.id$', 'address_id' => '$qta2.id$', 'qty' => 1]
                ]
            ]
        ),
    ]
    public function testCollectTotalsWithMultipleAddresses(): void
    {
        $cart = $this->fixtures->get('qt');
        $cart = $this->cartRepository->get($cart->getId());
        $cart->setTotalsCollectedFlag(false);
        $cart->collectTotals();
        $addresses = $cart->getAllShippingAddresses();
        $this->assertCount(2, $addresses);
        $address = $addresses[0];
        $totals = $address->getTotals();
        $this->assertArrayHasKey('weee_tax', $totals);
        $this->assertEquals(0.3, $totals['weee_tax']['value']);
        $address = $addresses[1];
        $totals = $address->getTotals();
        $this->assertArrayHasKey('weee_tax', $totals);
        $this->assertEquals(0.8, $totals['weee_tax']['value']);
    }

    /**
     * Verify that recalculateParent propagates all WEEE unit and row amounts from child to parent quote item.
     */
    #[
        Config('tax/weee/enable', '1', 'store', 'default'),
        Config('tax/weee/apply_vat', '0', 'store', 'default'),
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr'], 'fpt'),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'conf_attr'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100.0,
                'fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.0]],
            ],
            'simple'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$conf_attr$'],
                '_links' => ['$simple$'],
            ],
            'configurable'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable.id$',
                'child_product_id' => '$simple.id$',
            ]
        ),
    ]
    public function testCollectSetsBaseWeeeTaxAppliedAmountOnConfigurableParentItem(): void
    {
        $cart = $this->fixtures->get('cart');
        $cart = $this->cartRepository->get($cart->getId());
        $cart->getShippingAddress()->setCountryId('US');
        $cart->setTotalsCollectedFlag(false)->collectTotals();

        $parentItem = null;
        $childItem = null;
        foreach ($cart->getAllItems() as $item) {
            if ($item->getParentItem()) {
                $childItem = $item;
            } else {
                $parentItem = $item;
            }
        }

        $this->assertNotNull($parentItem, 'Configurable parent quote item must exist');
        $this->assertNotNull($childItem, 'Simple child quote item must exist');
        $this->assertGreaterThan(
            0,
            $childItem->getBaseWeeeTaxAppliedAmount(),
            'Child item base_weee_tax_applied_amount must be set by the Weee collector'
        );
        $this->assertEquals(
            $childItem->getBaseWeeeTaxAppliedAmount(),
            $parentItem->getBaseWeeeTaxAppliedAmount(),
            'Configurable parent item base_weee_tax_applied_amount must be propagated from the child item'
        );
    }

    /**
     * Same as testCollectSetsBaseWeeeTaxAppliedAmountOnConfigurableParentItem with taxable FPT (apply_vat=1).
     */
    #[
        Config('tax/weee/enable', '1', 'store', 'default'),
        Config('tax/weee/apply_vat', '1', 'store', 'default'),
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr'], 'fpt'),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'conf_attr'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100.0,
                'fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.0]],
            ],
            'simple'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$conf_attr$'],
                '_links' => ['$simple$'],
            ],
            'configurable'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable.id$',
                'child_product_id' => '$simple.id$',
            ]
        ),
    ]
    public function testCollectSetsWeeeRowAmountsOnConfigurableParentItemWithTaxableFpt(): void
    {
        $cart = $this->fixtures->get('cart');
        $cart = $this->cartRepository->get($cart->getId());
        $cart->getShippingAddress()->setCountryId('US');
        $cart->setTotalsCollectedFlag(false)->collectTotals();

        $parentItem = null;
        $childItem = null;
        foreach ($cart->getAllItems() as $item) {
            if ($item->getParentItem()) {
                $childItem = $item;
            } else {
                $parentItem = $item;
            }
        }

        $this->assertNotNull($parentItem, 'Configurable parent quote item must exist');
        $this->assertNotNull($childItem, 'Simple child quote item must exist');

        // Child carries the FPT row amounts set by process()
        $this->assertGreaterThan(0, $childItem->getWeeeTaxAppliedRowAmount());
        $this->assertGreaterThan(0, $childItem->getBaseWeeeTaxAppliedRowAmnt());

        // Parent unit amounts are propagated from previous fixes
        $this->assertEquals(
            $childItem->getWeeeTaxAppliedAmount(),
            $parentItem->getWeeeTaxAppliedAmount(),
            'Configurable parent item weee_tax_applied_amount must be propagated from the child item'
        );
        $this->assertEquals(
            $childItem->getBaseWeeeTaxAppliedAmount(),
            $parentItem->getBaseWeeeTaxAppliedAmount(),
            'Configurable parent item base_weee_tax_applied_amount must be propagated from the child item'
        );

        $parentApplied = $this->weeeHelper->getApplied($parentItem);
        $this->assertNotEmpty($parentApplied, 'weee_tax_applied JSON must be set on the parent quote item');
        $parentJsonRowAmount = array_sum(array_column($parentApplied, 'row_amount'));
        $this->assertEquals(
            $childItem->getWeeeTaxAppliedRowAmount(),
            $parentJsonRowAmount,
            'Parent weee_tax_applied JSON row_amount must match child row amount for correct invoicing'
        );
    }

    /**
     * Verify invoice and credit memo grand totals include FPT for a configurable product (apply_vat=0).
     */
    #[
        Config('tax/weee/enable', '1', 'store', 'default'),
        Config('tax/weee/apply_vat', '0', 'store', 'default'),
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr'], 'fpt'),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'conf_attr'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100.0,
                'fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.0]],
            ],
            'simple'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$conf_attr$'],
                '_links' => ['$simple$'],
            ],
            'configurable'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable.id$',
                'child_product_id' => '$simple.id$',
            ]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(CreditmemoFixture::class, ['order_id' => '$order.id$'], 'creditmemo'),
    ]
    public function testInvoiceAndCreditMemoIncludeFptForConfigurableProduct(): void
    {
        $order = $this->fixtures->get('order');
        $invoice = $this->fixtures->get('invoice');
        $creditmemo = $this->fixtures->get('creditmemo');

        $this->assertEquals(
            $order->getGrandTotal(),
            $invoice->getGrandTotal(),
            'Invoice grand total must equal order grand total — FPT must be included in the invoice'
        );
        $this->assertEquals(
            $order->getGrandTotal(),
            $creditmemo->getGrandTotal(),
            'Credit memo grand total must equal order grand total — FPT must be included in the refund'
        );

        $parentOrderItem = null;
        foreach ($order->getItems() as $item) {
            if (!$item->getParentItemId()) {
                $parentOrderItem = $item;
                break;
            }
        }
        $this->assertNotNull($parentOrderItem, 'Configurable parent order item must exist');
        $this->assertNotEmpty(
            $this->weeeHelper->getApplied($parentOrderItem),
            'Parent order item weee_tax_applied JSON must be populated so Invoice/Creditmemo collectors can read it'
        );
    }

    /**
     * Verify invoice and credit memo grand totals include FPT for a configurable product (apply_vat=1).
     */
    #[
        Config('tax/weee/enable', '1', 'store', 'default'),
        Config('tax/weee/apply_vat', '1', 'store', 'default'),
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr'], 'fpt'),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'conf_attr'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100.0,
                'fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.0]],
            ],
            'simple'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$conf_attr$'],
                '_links' => ['$simple$'],
            ],
            'configurable'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable.id$',
                'child_product_id' => '$simple.id$',
            ]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(CreditmemoFixture::class, ['order_id' => '$order.id$'], 'creditmemo'),
    ]
    public function testInvoiceAndCreditMemoIncludeFptForConfigurableProductWithTaxableFpt(): void
    {
        $order = $this->fixtures->get('order');
        $invoice = $this->fixtures->get('invoice');
        $creditmemo = $this->fixtures->get('creditmemo');

        $this->assertEquals(
            $order->getGrandTotal(),
            $invoice->getGrandTotal(),
            'Invoice grand total must equal order grand total — FPT must be included in the invoice'
        );
        $this->assertEquals(
            $order->getGrandTotal(),
            $creditmemo->getGrandTotal(),
            'Credit memo grand total must equal order grand total — FPT must be included in the refund'
        );

        $parentOrderItem = null;
        foreach ($order->getItems() as $item) {
            if (!$item->getParentItemId()) {
                $parentOrderItem = $item;
                break;
            }
        }
        $this->assertNotNull($parentOrderItem, 'Configurable parent order item must exist');
        $this->assertNotEmpty(
            $this->weeeHelper->getApplied($parentOrderItem),
            'Parent order item weee_tax_applied JSON must be populated so Invoice/Creditmemo collectors can read it'
        );
    }

    /**
     * @param int $cartId
     * @return TotalsInterface
     */
    private function getTotals(int $cartId): TotalsInterface
    {
        /** @var Address $address */
        $address = $this->objectManager->get(AddressFactory::class)->create();
        $address->setAddressType(Address::ADDRESS_TYPE_SHIPPING)
            ->setCountryId('US')
            ->setRegionId(12)
            ->setRegion('California')
            ->setPostcode('90230');
        $addressInformation = $this->objectManager->create(
            TotalsInformationInterface::class,
            [
                'data' => [
                    'address' => $address,
                    'shipping_method_code' => 'flatrate',
                    'shipping_carrier_code' => 'flatrate',
                ],
            ]
        );

        return $this->totalsManagement->calculate($cartId, $addressInformation);
    }
}
