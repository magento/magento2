<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Model\Total\Quote;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->totalsManagement = $this->objectManager->get(TotalsInformationManagement::class);
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
        $addressFactory = $this->objectManager->get(AddressFactory::class);
        /** @var Address $newAddress */
        $newAddress = $addressFactory->create()->setAddressType('shipping');
        $newAddress->setCountryId('US')->setRegionId(12)->setRegion('California')->setPostcode('90230');
        $addressInformation = $this->objectManager->create(
            TotalsInformationInterface::class,
            [
                'data' => [
                    'address' => $newAddress,
                    'shipping_method_code' => 'flatrate',
                    'shipping_carrier_code' => 'flatrate',
                ],
            ]
        );

        $actual = $this->totalsManagement->calculate($cartId, $addressInformation);

        $items = $actual->getTotalSegments();
        $this->assertTrue(array_key_exists('weee_tax', $items));
        $this->assertEquals(25.4, $items['weee_tax']->getValue());
    }
}
