<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class ConverterTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectCopyService = $this->objectManager->create(\Magento\Framework\DataObject\Copy::class);
    }

    public function testThatFieldBaseShippingDiscountTaxCompensationAmountPresentInOrderAddress()
    {
        $amountValue = 999.99;
        /** @var \Magento\Quote\Model\Quote\Address $quoteAddress */
        $quoteAddress = $this->objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        $quoteAddress->setBaseShippingDiscountTaxCompensationAmount($amountValue);
        
        $orderAddressData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_order',
            $quoteAddress
        );

        $this->assertArrayHasKey('base_shipping_discount_tax_compensation_amnt', $orderAddressData);
        $this->assertEquals($amountValue, $orderAddressData['base_shipping_discount_tax_compensation_amnt']);
    }
}
