<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\FilterBuilder;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\User\Api\Data\UserInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 */
class QuoteRepositoryTest extends \PHPUnit\Framework\TestCase
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
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = BootstrapHelper::getObjectManager();
        $this->quoteRepository = $this->objectManager->create(QuoteRepository::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->objectManager->create(FilterBuilder::class);
    }

    /**
     * Tests that quote saved with custom store id has same store id after getting via repository.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testGetQuoteWithCustomStoreId()
    {
        $secondStoreCode = 'fixture_second_store';
        $reservedOrderId = 'test01';

        $storeRepository = $this->objectManager->create(StoreRepository::class);
        $secondStore = $storeRepository->get($secondStoreCode);

        // Set store_id in quote to second store_id
        $quote = $this->getQuote($reservedOrderId);
        $quote->setStoreId($secondStore->getId());
        $this->quoteRepository->save($quote);

        $savedQuote = $this->quoteRepository->get($quote->getId());

        $this->assertEquals(
            $secondStore->getId(),
            $savedQuote->getStoreId(),
            'Quote store id should be equal with store id value in DB'
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        $searchCriteria = $this->getSearchCriteria('test01');
        $searchResult = $this->quoteRepository->getList($searchCriteria);
        $this->performAssertions($searchResult);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
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
     * @magentoAppIsolation enabled
     */
    public function testSaveWithNotExistingCustomerAddress()
    {
        $addressData = include __DIR__ . '/../../Sales/_files/address_data.php';

        /** @var QuoteAddress $billingAddress */
        $billingAddress = $this->objectManager->create(QuoteAddress::class, ['data' => $addressData]);
        $billingAddress->setAddressType(QuoteAddress::ADDRESS_TYPE_BILLING)
            ->setCustomerAddressId('not_existing');

        /** @var QuoteAddress $shippingAddress */
        $shippingAddress = $this->objectManager->create(QuoteAddress::class, ['data' => $addressData]);
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
    }

    /**
     * Returns quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId)
    {
        $searchCriteria = $this->getSearchCriteria($reservedOrderId);
        $searchResult = $this->quoteRepository->getList($searchCriteria);
        $items = $searchResult->getItems();

        /** @var CartInterface $quote */
        $quote = array_pop($items);

        return $quote;
    }

    /**
     * Get search criteria
     *
     * @param string $filterValue
     * @return SearchCriteria
     */
    private function getSearchCriteria($filterValue)
    {
        $filters = [];
        $filters[] = $this->filterBuilder->setField('reserved_order_id')
            ->setConditionType('=')
            ->setValue($filterValue)
            ->create();
        $this->searchCriteriaBuilder->addFilters($filters);

        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Perform assertions
     *
     * @param SearchResults|CartSearchResultsInterface $searchResult
     */
    private function performAssertions($searchResult)
    {
        $expectedExtensionAttributes = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'admin@example.com'
        ];

        $items = $searchResult->getItems();

        /** @var CartInterface $actualQuote */
        $actualQuote = array_pop($items);

        /** @var UserInterface $testAttribute */
        $testAttribute = $actualQuote->getExtensionAttributes()->getQuoteTestAttribute();

        $this->assertInstanceOf(CartInterface::class, $actualQuote);
        $this->assertEquals('test01', $actualQuote->getReservedOrderId());
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute->getLastName());
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute->getEmail());
    }
}
