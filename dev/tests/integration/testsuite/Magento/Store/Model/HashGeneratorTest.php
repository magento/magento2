<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreSwitcher\HashGenerator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use \Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Test class for \Magento\Store\Model\StoreSwitcher\HashGenerator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HashGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var int
     */
    private $customerId;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Model\Authorization\CustomerSessionUserContext
     */
    private $customerSessionUserContext;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    private $key;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * Class dependencies initialization
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $session = $this->objectManager->create(
            Session::class
        );
        $this->accountManagement = $this->objectManager->create(AccountManagementInterface::class);
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');
        $session->setCustomerDataAsLoggedIn($customer);
        $this->customerSessionUserContext = $this->objectManager->create(
            \Magento\Customer\Model\Authorization\CustomerSessionUserContext::class,
            ['customerSession' => $session]
        );
        $this->hashGenerator = $this->objectManager->create(
            StoreSwitcher\HashGenerator::class,
            ['currentUser' => $this->customerSessionUserContext]
        );
        $this->customerId = $customer->getId();
        $this->deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $this->key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $this->urlHelper=$this->objectManager->create(UrlHelper::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSwitch(): void
    {
        $redirectUrl = "http://domain.com/";
        $fromStoreCode = 'test';
        $toStoreCode = 'fixture_second_store';
        $encodedUrl=$this->urlHelper->getEncodedUrl($redirectUrl);
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $fromStore = $storeRepository->get($fromStoreCode);
        $toStore = $storeRepository->get($toStoreCode);
        $timeStamp = time();
        $data = implode(',', [$this->customerId, $timeStamp, $fromStoreCode]);
        $signature = hash_hmac('sha256', $data, $this->key);
        $customerId = $this->customerId;

        $expectedUrl = "http://domain.com/stores/store/switchrequest";
        $expectedUrl = $this->urlHelper->addRequestParam(
            $expectedUrl,
            ['customer_id' => $customerId]
        );
        $expectedUrl = $this->urlHelper->addRequestParam($expectedUrl, ['time_stamp' => $timeStamp]);
        $expectedUrl = $this->urlHelper->addRequestParam($expectedUrl, ['signature' => $signature]);
        $expectedUrl = $this->urlHelper->addRequestParam($expectedUrl, ['___from_store' => $fromStoreCode]);
        $expectedUrl = $this->urlHelper->addRequestParam(
            $expectedUrl,
            [ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl]
        );
        $this->assertEquals($expectedUrl, $this->hashGenerator->switch($fromStore, $toStore, $redirectUrl));
    }

    /**
     * @return void
     */
    public function testValidateHashWithCorrectData(): void
    {
        $timeStamp = time();
        $customerId = $this->customerId;
        $fromStoreCode = 'test';
        $data = implode(',', [$customerId, $timeStamp, $fromStoreCode]);
        $signature = hash_hmac('sha256', $data, $this->key);
        $this->assertTrue($this->hashGenerator->validateHash($signature, [$customerId, $timeStamp, $fromStoreCode]));
    }

    /**
     * @return void
     */
    public function testValidateHashWithInCorrectData(): void
    {
        $timeStamp = 0;
        $customerId = 8;
        $fromStoreCode = 'test';
        $data = implode(',', [$customerId, $timeStamp, $fromStoreCode]);
        $signature = hash_hmac('sha256', $data, $this->key);
        $this->assertFalse($this->hashGenerator->validateHash($signature, [$customerId, $timeStamp, $fromStoreCode]));
    }
}
