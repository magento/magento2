<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Adminhtml\Payment;

use Braintree\Configuration;
use Magento\Backend\Model\Session\Quote;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Controller\Adminhtml\Payment\GetClientToken
 */
class GetClientTokenTest extends AbstractBackendController
{
    /**
     * @var Quote
     */
    private $quoteSession;

    /**
     * @var ObjectManager|MockObject $stubObjectManager
     */
    private $stubObjectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->quoteSession = $this->_objectManager->get(Quote::class);

        $this->stubObjectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapterFactory = new BraintreeAdapterFactory(
            $this->stubObjectManager,
            $this->_objectManager->get(Config::class)
        );

        $this->_objectManager->addSharedInstance($adapterFactory, BraintreeAdapterFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(BraintreeAdapterFactory::class);
        parent::tearDown();
    }

    /**
     * Checks if client token will retrieved from Braintree initialized with default scope.
     *
     * @magentoDataFixture Magento/Braintree/_files/payment_configuration.php
     * @magentoAppArea adminhtml
     */
    public function testExecute()
    {
        $this->perform(
            'def_merchant_id',
            'def_public_key',
            'def_private_key'
        );
    }

    /**
     * Checks if client token will be retrieved from Braintree initialized per store.
     *
     * @magentoDataFixture Magento/Braintree/_files/payment_configuration.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithStoreConfiguration()
    {
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->_objectManager->get(StoreRepositoryInterface::class);
        $store = $storeRepository->get('test');
        $this->quoteSession->setStoreId($store->getId());

        $this->perform(
            'store_merchant_id',
            'store_public_key',
            'def_private_key' // should be read from default scope
        );
    }

    /**
     * Checks if client token will be retrieved from Braintree initialized per website.
     *
     * @magentoDataFixture Magento/Braintree/_files/payment_configuration.php
     * @magentoAppArea adminhtml
     */
    public function testExecuteWithWebsiteConfiguration()
    {
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->_objectManager->get(StoreRepositoryInterface::class);
        $store = $storeRepository->get('fixture_second_store');
        $this->quoteSession->setStoreId($store->getId());

        $this->perform(
            'website_merchant_id',
            'def_public_key', // should be read from default scope
            'website_private_key'
        );
    }

    /**
     * Perform test.
     *
     * @param string $merchantId
     * @param string $publicKey
     * @param string $privateKey
     * @return void
     */
    private function perform($merchantId, $publicKey, $privateKey)
    {
        $args = [
            'merchantId' => $merchantId,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
            'environment' => 'sandbox',
        ];

        $adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->setConstructorArgs($args)
            ->setMethods(['generate'])
            ->getMock();
        $adapter->method('generate')
            ->willReturn('client_token');

        $this->stubObjectManager->method('create')
            ->with(BraintreeAdapter::class, $args)
            ->willReturn($adapter);

        $this->dispatch('backend/braintree/payment/getClientToken');

        /** @var SerializerInterface $serializer */
        $serializer = $this->_objectManager->get(SerializerInterface::class);
        $decoded = $serializer->unserialize($this->getResponse()->getBody());
        $this->performAsserts($decoded['clientToken'], $merchantId, $publicKey, $privateKey);
    }

    /**
     * Perform Asserts.
     *
     * @param string $clientToken
     * @param string $merchantId
     * @param string $publicKey
     * @param string $privateKey
     * @return void
     */
    private function performAsserts($clientToken, $merchantId, $publicKey, $privateKey)
    {
        self::assertEquals('client_token', $clientToken);
        self::assertEquals(Configuration::merchantId(), $merchantId);
        self::assertEquals(Configuration::publicKey(), $publicKey);
        self::assertEquals(Configuration::privateKey(), $privateKey);
    }
}
