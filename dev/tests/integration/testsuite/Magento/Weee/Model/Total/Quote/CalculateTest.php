<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Model\Total\Quote;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Quote totals calculate tests class
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

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/apply_vat 1
     * @magentoDataFixture Magento\Weee\Test\Fixture\Attribute as:fpt with:{"attribute_code": "fpt_attr"}
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product as:p1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product as:p2
     * @magentoDataFixture Magento\Quote\Test\Fixture\GuestCart as:qt
     * @magentoDataFixture Magento\Quote\Test\Fixture\AddProductToCart with:{"cart_id":"$qt.id$","product_id":"$p1.id$"}
     * @magentoDataFixture Magento\Quote\Test\Fixture\AddProductToCart with:{"cart_id":"$qt.id$","product_id":"$p2.id$"}
     * @magentoDataFixtureDataProvider {"p1":{"fpt_attr":[{"website_id":0,"country":"US","state":0,"price":0.3}]}}
     * @magentoDataFixtureDataProvider {"p2":{"fpt_attr":[{"website_id":0,"country":"US","state":0,"price":0.8}]}}
     * @return void
     */
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

    /**
     * @magentoConfigFixture default_store tax/weee/enable 1
     * @magentoConfigFixture default_store tax/weee/apply_vat 1
     * @magentoDataFixture Magento\Weee\Test\Fixture\Attribute as:fpt
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product as:p1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product as:p2
     * @magentoDataFixture Magento\Quote\Test\Fixture\GuestCart as:qt
     * @magentoDataFixture Magento\Quote\Test\Fixture\AddProductToCart as:qti1
     * @magentoDataFixture Magento\Quote\Test\Fixture\AddProductToCart as:qti2
     * @magentoDataFixture Magento\Multishipping\Test\Fixture\AddAddressToCart as:qta1
     * @magentoDataFixture Magento\Multishipping\Test\Fixture\AddAddressToCart as:qta2
     * @magentoDataFixture Magento\Multishipping\Test\Fixture\ShippingAssignments as:shippingAssignments
     * @magentoDataFixtureDataProvider collectTotalsWithMultipleAddressesFixtureDataProvider
     */
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
     * @return array
     */
    public function collectTotalsWithMultipleAddressesFixtureDataProvider(): array
    {
        return [
            'fpt' => ['attribute_code' => 'fpt_attr'],
            'p1' => ['fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.3]]],
            'p2' => ['fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.8]]],
            'qti1' => ['cart_id' => '$qt.id$', 'product_id' => '$p1.id$'],
            'qti2' => ['cart_id' => '$qt.id$', 'product_id' => '$p2.id$'],
            'qta1' => ['cart_id' => '$qt.id$'],
            'qta2' => ['cart_id' => '$qt.id$'],
            'shippingAssignments' => [
                'cart_id' => '$qt.id$',
                'assignments' => [
                    ['item_id' => '$qti1.id$', 'address_id' => '$qta1.id$', 'qty' => 1],
                    ['item_id' => '$qti2.id$', 'address_id' => '$qta2.id$', 'qty' => 1]
                ]
            ],
        ];
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
