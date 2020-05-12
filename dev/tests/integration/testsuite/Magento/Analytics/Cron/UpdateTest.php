<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector\Http\Client\Curl as CurlClient;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigDataResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @var ConfigDataResource
     */
    private $configValueResourceModel;

    /**
     * @var Update
     */
    private $updateCron;

    /**
     * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpClient;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->httpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMockForAbstractClass();
        $this->objectManager->addSharedInstance($this->httpClient, CurlClient::class);
        $this->preparedValueFactory = $this->objectManager->get(PreparedValueFactory::class);
        $this->configValueResourceModel = $this->objectManager->get(ConfigDataResource::class);
        $this->updateCron = $this->objectManager->get(Update::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->flagManager = $this->objectManager->get(FlagManager::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     *
     */
    public function testSuccessfulAttemptExecute()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'http://store.com/'
        );

        $this->mockRequestCall(201, 'URL has been changed');

        $this->updateCron->execute();
        $this->assertEmpty($this->getUpdateCounterFlag());
        $this->assertEmpty($this->getPreviousBaseUrlFlag());
        $this->assertEmpty($this->getConfigValue(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     *
     */
    public function testUnsuccessfulAttemptExecute()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'http://store.com/'
        );

        $reverseCounter = $this->getUpdateCounterFlag();
        $this->mockRequestCall(500, 'Unauthorized access');

        $this->updateCron->execute();
        $this->assertEquals($reverseCounter - 1, $this->getUpdateCounterFlag());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     *
     */
    public function testLastUnsuccessfulAttemptExecute()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'http://store.com/'
        );

        $this->setUpdateCounterValue(1);
        $this->mockRequestCall(500, 'Unauthorized access');

        $this->updateCron->execute();
        $this->assertEmpty($this->getUpdateCounterFlag());
        $this->assertEmpty($this->getPreviousBaseUrlFlag());
        $this->assertEmpty($this->getConfigValue(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH));
    }

    /**
     * Save configuration value
     *
     * @param string $path The configuration path in format section/group/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @return void
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function saveConfigValue(
        string $path,
        string $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ) {
        $configValue = $this->preparedValueFactory->create(
            $path,
            $value,
            $scope
        );
        $this->configValueResourceModel->save($configValue);
    }

    /**
     * Get configuration value
     *
     * @param string $path
     * @param string $scopeType
     * @return mixed
     */
    private function getConfigValue(
        string $path,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ) {
        return $this->scopeConfig->getValue(
            $path,
            $scopeType
        );
    }

    /**
     * Get update counter flag value
     *
     * @return int|null
     */
    private function getUpdateCounterFlag(): ?int
    {
        return $this->flagManager
            ->getFlagData(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
    }

    /**
     * Get previous URL flag value
     *
     * @return string|null
     */
    private function getPreviousBaseUrlFlag(): ?string
    {
        return $this->flagManager
            ->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE);
    }

    /**
     * Set response mock for the HTTP client
     *
     * @param int $responseCode
     * @param string $responseMessage
     */
    private function mockRequestCall(int $responseCode, string $responseMessage): void
    {
        $response = $this->objectManager->create(
            \Zend_Http_Response::class,
            [
                'code' => $responseCode,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode(['message' => $responseMessage])
            ]
        );

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);
    }

    /**
     * Set value for update counter flag
     *
     * @param int $value
     */
    private function setUpdateCounterValue(int $value): void
    {
        $this->flagManager
            ->saveFlag(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $value);
    }
}
