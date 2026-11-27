<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for the quote totals collected across all quote addresses.
 */
#[DbIsolation(true)]
class TotalsCollectorTest extends TestCase
{
    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * The quote shipping amount must come from the shipping address even when the
     * billing address is requested after it, as in the steps of the issue.
     *
     * @see https://github.com/magento/magento2/issues/26209
     */
    #[
        ConfigFixture('carriers/flatrate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/price', '5', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
    ]
    public function testShippingAmountIsKeptWhenBillingAddressIsRequestedAfterShippingAddress(): void
    {
        $product = $this->productRepository->get($this->fixtures->get('product')->getSku());

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->addProduct($product, 1);

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData([
            'firstname' => 'Test',
            'lastname' => 'Test',
            'street' => ['Test Ave 12'],
            'city' => 'Test',
            'postcode' => '12345',
            'country_id' => 'SE',
            'telephone' => '+1234567890',
        ]);
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->setShippingMethod('flatrate_flatrate');

        // Requesting the billing address only now creates it after the shipping address,
        // so it is the last address the totals collector iterates over.
        $quote->getBillingAddress();
        $this->assertSame(
            [Address::ADDRESS_TYPE_SHIPPING, Address::ADDRESS_TYPE_BILLING],
            array_map(static fn (Address $address) => $address->getAddressType(), $quote->getAllAddresses()),
            'The billing address must come after the shipping address to reproduce the issue.'
        );

        $quote->collectTotals();

        $this->assertEquals(
            5.0,
            $shippingAddress->getShippingAmount(),
            'The flat rate must have been collected on the shipping address.'
        );
        $this->assertEquals(5.0, $quote->getShippingAmount());
        $this->assertEquals(5.0, $quote->getBaseShippingAmount());
        $this->assertSame($shippingAddress->getShippingDescription(), $quote->getShippingDescription());
    }
}
