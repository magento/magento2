<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow\Service\Request;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @magentoAppIsolation enabled
 */
class SecureTokenTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SecureToken
     */
    private $service;

    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Random|MockObject;
     */
    private $mathRandom;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->objectManager->addSharedInstance($this->gateway, Gateway::class);

        $this->mathRandom = $this->getMockBuilder(Random::class)
            ->getMock();

        $this->service = $this->objectManager->create(
            SecureToken::class,
            [
                'mathRandom' => $this->mathRandom,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(Gateway::class);
    }

    /**
     * Checks a case when secure token can be obtained with credentials for the default scope.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payflowpro.php
     * @magentoDataFixture Magento/Paypal/Fixtures/default_payment_configuration.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testRequestToken(): void
    {
        $quote = $this->getQuote('100000015');
        $quote->setStoreId(null);
        $this->execute($quote, 'def_partner', 'def_vendor', 'def_user', 'def_pwd');
    }

    /**
     * Checks a case when secure token can be obtained with credentials specified per store.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payflowpro.php
     * @magentoDataFixture Magento/Paypal/Fixtures/store_payment_configuration.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testRequestTokenWithStoreConfiguration(): void
    {
        $quote = $this->getQuote('100000015');
        $store = $this->getStore('test');
        $quote->setStoreId($store->getId());
        $this->execute($quote, 'store_partner', 'store_vendor', 'store_user', 'store_pwd');
    }

    /**
     * Checks a case when secure token can be obtained with credentials specified per website.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payflowpro.php
     * @magentoDataFixture Magento/Paypal/Fixtures/website_payment_configuration.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testRequestTokenWithWebsiteConfiguration(): void
    {
        $quote = $this->getQuote('100000015');
        $store = $this->getStore('fixture_second_store');
        $quote->setStoreId($store->getId());
        $this->execute($quote, 'website_partner', 'website_vendor', 'website_user', 'website_pwd');
    }

    /**
     * Retrieves secure token and perform test assertions.
     *
     * @param Quote $quote
     * @param string $expPartner
     * @param string $expVendor
     * @param string $expUser
     * @param string $expPwd
     * @return void
     */
    private function execute(
        Quote $quote,
        string $expPartner,
        string $expVendor,
        string $expUser,
        string $expPwd
    ): void {
        $secureTokenId = '31f2a7c8d257c70b1c9eb9051b90e0';
        $token = '80IgSbabyj0CtBDWHZZeQN3';

        $this->mathRandom->method('getUniqueHash')
            ->willReturn($secureTokenId);

        $response = new DataObject([
            'result' => '0',
            'respmsg' => 'Approved',
            'securetoken' => $token,
            'securetokenid' => $secureTokenId,
            'result_code' => '0',
        ]);
        $self = $this;
        $this->gateway->method('postRequest')
            /** @var DataObject $request */
            ->with(self::callback(function ($request) use ($self, $expPartner, $expVendor, $expUser, $expPwd) {
                $self->performAssertion($expPartner, $request->getPartner(), '{Partner}');
                $self->performAssertion($expVendor, $request->getVendor(), '{Vendor}');
                $self->performAssertion($expUser, $request->getUser(), '{User}');
                $self->performAssertion($expPwd, $request->getPwd(), '{Password}');

                return true;
            }))
            ->willReturn($response);

        $response = $this->service->requestToken($quote);
        $this->performAssertion($token, $response->getData('securetoken'), '{Secure Token}');
    }

    /**
     * Perform assertions test assertions.
     *
     * @param string $expected
     * @param string $actual
     * @param string $property
     * @return void
     */
    private function performAssertion(string $expected, string $actual, string $property): void
    {
        self::assertEquals($expected, $actual, "$property should match.");
    }

    /**
     * Loads quote by order increment id.
     *
     * @param string $orderIncrementId
     * @return Quote
     */
    private function getQuote(string $orderIncrementId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $orderIncrementId)
            ->create();

        $items = $this->quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Loads store by provided code.
     *
     * @param string $code
     * @return StoreInterface
     */
    private function getStore(string $code): StoreInterface
    {
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        return $storeRepository->get($code);
    }
}
