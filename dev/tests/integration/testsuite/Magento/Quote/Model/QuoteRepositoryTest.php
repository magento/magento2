<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Store\Model\Website;

/**
 * QuoteRepository test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var array
     */
    private $addressData;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteRepository = $this->objectManager->create(QuoteRepository::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->objectManager->create(FilterBuilder::class);

        $this->addressData = require __DIR__ . '/../../Sales/_files/address_data.php';
    }

    /**
     * Tests getting quote.
     * @magentoDataFixture Magento/Quote/_files/quote_on_second_website.php
     */
    public function testGet()
    {
        /** @var Website $website */
        $website = $this->objectManager->create(Website::class);
        $website->load('test', 'code');

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setSharedStoreIds($website->getStoreIds());
        $quote->load('test01', 'reserved_order_id');

        $this->quoteRepository->get($quote->getId());
    }

    /**
     * Tests getting list of quotes according to search criteria.
     * @magentoDataFixture Magento/Quote/_files/quote.php
     */
    public function testGetList()
    {
        $searchCriteria = $this->getSearchCriteria('test01');
        $searchResult = $this->quoteRepository->getList($searchCriteria);
        $this->performAssertions($searchResult);
    }

    /**
     * Tests getting list of quotes according to different search criterias.
     * @magentoDataFixture Magento/Quote/_files/quote.php
     */
    public function testGetListDoubleCall()
    {
        $searchCriteria1 = $this->getSearchCriteria('test01');
        $searchCriteria2 = $this->getSearchCriteria('test02');

        $searchResult = $this->quoteRepository->getList($searchCriteria1);
        $this->performAssertions($searchResult);

        $searchResult = $this->quoteRepository->getList($searchCriteria2);
        $this->assertEmpty($searchResult->getItems());
    }

    /**
     * Save quote test.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveWithNotExistingCustomerAddress()
    {
        /** @var QuoteAddress $billingAddress */
        $billingAddress = $this->objectManager->create(QuoteAddress::class, ['data' => $this->addressData]);
        $billingAddress->setAddressType(QuoteAddress::ADDRESS_TYPE_BILLING)
            ->setCustomerAddressId('not_existing');

        /** @var QuoteAddress $shippingAddress */
        $shippingAddress = $this->objectManager->create(QuoteAddress::class, ['data' => $this->addressData]);
        $shippingAddress->setAddressType(QuoteAddress::ADDRESS_TYPE_SHIPPING)
            ->setCustomerAddressId('not_existing');

        /** @var Shipping $shipping */
        $shipping = $this->objectManager->create(Shipping::class);
        $shipping->setAddress($shippingAddress);

        /** @var ShippingAssignment $shippingAssignment */
        $shippingAssignment = $this->objectManager->create(ShippingAssignment::class);
        $shippingAssignment->setItems([]);
        $shippingAssignment->setShipping($shipping);

        /** @var CartExtension $extensionAttributes */
        $extensionAttributes = $this->objectManager->create(CartExtension::class);
        $extensionAttributes->setShippingAssignments([$shippingAssignment]);

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setStoreId(1)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setExtensionAttributes($extensionAttributes)
            ->save();
        $this->quoteRepository->save($quote);

        $this->assertNull($quote->getBillingAddress()->getCustomerAddressId());
        $this->assertNull(
            $quote->getExtensionAttributes()
                ->getShippingAssignments()[0]
                ->getShipping()
                ->getAddress()
                ->getCustomerAddressId()
        );

        $this->quoteRepository->delete($quote);
    }

    /**
     * Get search criteria.
     *
     * @param string $filterValue
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function getSearchCriteria($filterValue)
    {
        $filters = [];
        $filters[] = $this->filterBuilder
            ->setField('reserved_order_id')
            ->setConditionType('=')
            ->setValue($filterValue)
            ->create();
        $this->searchCriteriaBuilder->addFilters($filters);

        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Perform assertions.
     *
     * @param SearchResults|CartSearchResultsInterface $searchResult
     */
    protected function performAssertions($searchResult)
    {
        $items = $searchResult->getItems();
        $this->assertNotEmpty($items, 'Search result is empty.');

        /** @var CartInterface $actualQuote */
        $actualQuote = array_pop($items);
        /** @var UserInterface $testAttribute */
        $testAttribute = $actualQuote->getExtensionAttributes()->getQuoteTestAttribute();

        $this->assertInstanceOf(CartInterface::class, $actualQuote);
        $this->assertEquals('test01', $actualQuote->getReservedOrderId());
        $this->assertEquals($this->addressData['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($this->addressData['lastname'], $testAttribute->getLastName());
        $this->assertEquals($this->addressData['email'], $testAttribute->getEmail());
    }
}
