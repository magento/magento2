<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteRepository = $this->objectManager->create(QuoteRepository::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->objectManager->create(FilterBuilder::class);
    }

    /**
     * Tests getting list of quotes according to search criteria.
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        $searchCriteria = $this->getSearchCriteria('test01');
        $searchResult = $this->quoteRepository->getList($searchCriteria);
        $this->performAssertions($searchResult);
    }

    /**
     * Tests getting list of quotes according to different search criterias.
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
     * Save quote test.
     *
     * @magentoDbIsolation enabled
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
