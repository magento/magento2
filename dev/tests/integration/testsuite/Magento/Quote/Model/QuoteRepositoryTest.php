<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\TestFramework\Helper\Bootstrap;

class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
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

        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\SearchCriteriaBuilder');

        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = Bootstrap::getObjectManager()->create('Magento\Quote\Api\CartRepositoryInterface');
        $searchResult = $quoteRepository->getList($searchCriteriaBuilder->create());
        $items = $searchResult->getItems();
        /** @var \Magento\Quote\Api\Data\CartInterface $actualQuote */
        $actualQuote = array_pop($items);
        $this->assertInstanceOf('Magento\Quote\Api\Data\CartInterface', $actualQuote);
        $this->assertEquals('test01', $actualQuote->getReservedOrderId());
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        $testAttribute = $actualQuote->getExtensionAttributes()->getQuoteTestAttribute();
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute->getLastName());
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute->getEmail());
    }
}
