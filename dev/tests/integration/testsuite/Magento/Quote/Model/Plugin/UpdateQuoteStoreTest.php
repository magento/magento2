<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\FunctionalTestingFramework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;

/**
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateQuoteStoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    protected function setUp()
    {
        $this->objectManager = BootstrapHelper::getObjectManager();
        $this->quoteRepository = $this->objectManager->create(CartRepositoryInterface::class);
    }

    /**
     * Tests that active quote store id updates after store cookie change.
     *
     * @magentoDataFixture Magento/Quote/_files/empty_quote.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @throws \ReflectionException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateQuoteStoreAfterChangeStoreCookie()
    {
        $secondStoreCode = 'fixture_second_store';
        $reservedOrderId = 'reserved_order_id';

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currentStore = $storeManager->getStore();

        $quote = $this->getQuote($reservedOrderId);
        $this->assertEquals(
            $currentStore->getId(),
            $quote->getStoreId(),
            'Current store id and quote store id are not match'
        );

        /** @var Session $checkoutSession */
        $checkoutSession = $this->objectManager->get(Session::class);
        $checkoutSession->setQuoteId($quote->getId());

        $storeRepository = $this->objectManager->create(StoreRepository::class);
        $secondStore = $storeRepository->get($secondStoreCode);

        $storeCookieManager = $this->getStoreCookieManager($currentStore);
        $storeCookieManager->setStoreCookie($secondStore);

        $updatedQuote = $this->getQuote($reservedOrderId);
        $this->assertEquals(
            $secondStore->getId(),
            $updatedQuote->getStoreId(),
            'Active quote store id should be equal second store id'
        );
    }

    /**
     * Retrieves quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        $items = $this->quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * Returns instance of StoreCookieManagerInterface with mocked cookieManager dependency.
     *
     * Mock is needed since integration test framework use own cookie manager with
     * behavior that differs from real environment.
     *
     * @param $currentStore
     * @return StoreCookieManagerInterface
     * @throws \ReflectionException
     */
    private function getStoreCookieManager(StoreInterface $currentStore): StoreCookieManagerInterface
    {
        /** @var StoreCookieManagerInterface $storeCookieManager */
        $storeCookieManager = $this->objectManager->get(StoreCookieManagerInterface::class);
        $cookieManagerMock = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieManagerMock->method('getCookie')
            ->willReturn($currentStore->getCode());

        $reflection = new \ReflectionClass($storeCookieManager);
        $cookieManager = $reflection->getProperty('cookieManager');
        $cookieManager->setAccessible(true);
        $cookieManager->setValue($storeCookieManager, $cookieManagerMock);

        return $storeCookieManager;
    }
}
