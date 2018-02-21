<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\TestFramework\Helper\Bootstrap;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        $expectedExtensionAttributes = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'admin@example.com'
        ];

        /** @var \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository */
        $quoteItemRepository = Bootstrap::getObjectManager()->create('\Magento\Quote\Api\CartItemRepositoryInterface');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
        $quoteId = $quote->load('test01', 'reserved_order_id')->getId();

        /** @var \Magento\Quote\Api\Data\CartItemInterface[] $quoteItems */
        $quoteItems = $quoteItemRepository->getList($quoteId);
        /** @var \Magento\Quote\Api\Data\CartItemInterface $actualQuoteItem */
        $actualQuoteItem = array_pop($quoteItems);
        $this->assertInstanceOf('Magento\Quote\Api\Data\CartItemInterface', $actualQuoteItem);
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        $testAttribute = $actualQuoteItem->getExtensionAttributes()->getQuoteItemTestAttribute();
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute->getLastName());
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute->getEmail());
    }
}
