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
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Store\Model\StoreSwitcher\HashGenerator\HashData;

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
     * @var HashData
     */
    private $hashData;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Class dependencies initialization
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->create(
            CustomerSession::class
        );
        $this->accountManagement = $this->objectManager->create(AccountManagementInterface::class);
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');
        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->customerSessionUserContext = $this->objectManager->create(
            \Magento\Customer\Model\Authorization\CustomerSessionUserContext::class,
            ['customerSession' => $this->customerSession]
        );
        $this->hashGenerator = $this->objectManager->create(
            StoreSwitcher\HashGenerator::class,
            ['currentUser' => $this->customerSessionUserContext]
        );
        $this->customerId = $customer->getId();
        $this->deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $this->key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $this->urlHelper=$this->objectManager->create(UrlHelper::class);
        $this->hashData=$this->objectManager->create(HashData::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->logout();
        parent::tearDown();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testSwitch(): void
    {
        $redirectUrl = "http://domain.com/";
        $fromStoreCode = 'test';
        $fromStore  = $this->createPartialMock(Store::class, ['getCode']);
        $toStore = $this->createPartialMock(Store::class, ['getCode']);
        $fromStore->expects($this->once())->method('getCode')->willReturn($fromStoreCode);
        $targetUrl=$this->hashGenerator->switch($fromStore, $toStore, $redirectUrl);
        // phpcs:ignore
        $urlParts=parse_url($targetUrl, PHP_URL_QUERY);
        $signature='';
        // phpcs:ignore
        parse_str($urlParts, $params);

        if (isset($params['signature'])) {
            $signature=$params['signature'];
        }
        $this->assertEquals($params['customer_id'], $this->customerId);
        $this->assertEquals($params['___from_store'], $fromStoreCode);

        $data =  new HashData(
            [
                "customer_id" => $this->customerId,
                "time_stamp" => $params['time_stamp'],
                "___from_store" => $fromStoreCode
            ]
        );
        $this->assertTrue($this->hashGenerator->validateHash($signature, $data));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testValidateHashWithInCorrectData(): void
    {
        $timeStamp = 0;
        $customerId = 8;
        $fromStoreCode = 'store1';
        $data = new HashData(
            [
                "customer_id" => $customerId,
                "time_stamp" => $timeStamp,
                "___from_store" => $fromStoreCode
            ]
        );
        $redirectUrl = "http://domain.com/";
        $fromStore = $this->createPartialMock(Store::class, ['getCode']);
        $toStore = $this->createPartialMock(Store::class, ['getCode']);
        $fromStore->expects($this->once())->method('getCode')->willReturn($fromStoreCode);
        $targetUrl = $this->hashGenerator->switch($fromStore, $toStore, $redirectUrl);
        // phpcs:ignore
        $urlParts = parse_url($targetUrl,PHP_URL_QUERY);
        $signature = '';
        // phpcs:ignore
        parse_str($urlParts, $params);

        if (isset($params['signature'])) {
            $signature = $params['signature'];
        }
        $this->assertFalse($this->hashGenerator->validateHash($signature, $data));
    }
}
