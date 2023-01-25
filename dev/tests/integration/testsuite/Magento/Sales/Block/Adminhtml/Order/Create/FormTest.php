<?php
/**
 * Test class for Form
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Form
     */
    private $block;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QuoteSession|MockObject
     */
    private $session;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->session = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getQuote', 'getStoreId', 'getStore', 'getQuoteId'])
            ->getMock();
        $this->session->method('getCustomerId')
            ->willReturn(1);

        $this->session->method('getStoreId')
            ->willReturn(1);

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentCurrencyCode'])
            ->getMock();
        $store->method('getCurrentCurrencyCode')
            ->willReturn('USD');
        $this->session->method('getStore')
            ->willReturn($store);

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $layout->createBlock(
            Form::class,
            'order_create_block' . random_int(0, PHP_INT_MAX),
            ['sessionQuote' => $this->session]
        );
        parent::setUp();
    }

    /**
     * Checks if all needed order's data is correctly returned to the form.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testOrderDataJson()
    {
        $customerId = 1;
        $quote = $this->getQuote('test01');
        $this->session->method('getQuote')
            ->willReturn($quote);
        $this->session->method('getQuoteId')
            ->willReturn($quote->getId());
        $addressData = $this->getAddressData();
        $addressIds = $this->setUpMockAddress($customerId, $addressData);
        $expected = [
            'customer_id' => $customerId,
            'addresses' => [
                $addressIds[0] => $addressData[0],
                $addressIds[1] => $addressData[1]
            ],
            'store_id' => 1,
            'currency_symbol' => '$',
            'shipping_method_reseted' => true,
            'payment_method' => 'checkmo',
            'quote_id' => $quote->getId()
        ];

        self::assertEquals($expected, json_decode($this->block->getOrderDataJson(), true));
    }

    /**
     * Saves customer's addresses.
     *
     * @param int $customerId
     * @param array $addressData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setUpMockAddress(int $customerId, array $addressData)
    {
        /** @var RegionInterfaceFactory $regionFactory */
        $regionFactory = $this->objectManager->create(RegionInterfaceFactory::class);
        /** @var AddressInterfaceFactory $addressFactory */
        $addressFactory = $this->objectManager->create(AddressInterfaceFactory::class);
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = $this->objectManager->create(AddressRepositoryInterface::class);
        $region = $regionFactory->create()
            ->setRegionCode('AL')
            ->setRegion('Alabama')
            ->setRegionId(1);

        $ids = [];
        foreach ($addressData as $data) {
            $address = $addressFactory->create(['data' => $data]);
            $address->setRegion($region)
                ->setCustomerId($customerId)
                ->setIsDefaultBilling(true)
                ->setIsDefaultShipping(true);
            $address = $addressRepository->save($address);
            $ids[] = $address->getId();
        }

        return $ids;
    }

    /**
     * Gets test address data.
     *
     * @return array
     */
    private function getAddressData(): array
    {
        return [
            [
                'firstname' => 'John',
                'lastname' => 'Smith',
                'company' => false,
                'street' => 'Green str, 67',
                'city' => 'CityM',
                'country_id' => 'US',
                'region' => 'Alabama',
                'region_id' => 1,
                'postcode' => '75477',
                'telephone' => '3468676',
                'vat_id' => false
            ],
            [
                'firstname' => 'John',
                'lastname' => 'Smith',
                'company' => false,
                'street' => 'Black str, 48',
                'city' => 'CityX',
                'country_id' => 'US',
                'region' => 'Alabama',
                'region_id' => 1,
                'postcode' => '47676',
                'telephone' => '3234676',
                'vat_id' => false,
            ]
        ];
    }

    /**
     * Gets quote by ID.
     *
     * @param string $reservedOrderId
     * @return Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();
        /** @var CartRepositoryInterface $repository */
        $repository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
