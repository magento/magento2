<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Checks that cron job was set if error handler was set and appropriate http error code was returned.
 */
class ReSignUpResponseResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseResolver
     */
    private $otpResponseResolver;

    /**
     * @var ResponseResolver
     */
    private $updateResponseResolver;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var ResponseResolver
     */
    private $notifyDataChangedResponseResolver;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->otpResponseResolver = $objectManager->get(
            'OtpResponseResolver'
        );
        $this->updateResponseResolver = $objectManager->get(
            'UpdateResponseResolver'
        );
        $this->notifyDataChangedResponseResolver = $objectManager->get(
            'NotifyDataChangedResponseResolver'
        );
        $this->converter = $objectManager->get(ConverterInterface::class);
        $this->flagManager = $objectManager->get(FlagManager::class);
    }

    /**
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     * @magentoDbIsolation enabled
     */
    public function testReSignUpOnOtp()
    {
        $body = $this->converter->toBody(['test' => '42']);
        $retryResponse = new \Zend_Http_Response(401, [$this->converter->getContentTypeHeader()], $body);
        $this->otpResponseResolver->getResult($retryResponse);
        $this->assertCronWasSet();
    }

    /**
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     * @magentoDbIsolation enabled
     */
    public function testReSignOnOtpWasNotCalled()
    {
        $body = $this->converter->toBody(['test' => '42']);
        $successResponse = new \Zend_Http_Response(201, [$this->converter->getContentTypeHeader()], $body);
        $this->otpResponseResolver->getResult($successResponse);
        $this->assertCronWasNotSet();
    }

    /**
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     * @magentoDbIsolation enabled
     */
    public function testReSignUpOnUpdateWasCalled()
    {
        $body = $this->converter->toBody(['test' => '42']);
        $retryResponse = new \Zend_Http_Response(401, [$this->converter->getContentTypeHeader()], $body);
        $this->updateResponseResolver->getResult($retryResponse);
        $this->assertCronWasSet();
    }

    /**
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     * @magentoDbIsolation enabled
     */
    public function testReSignUpOnUpdateWasNotCalled()
    {
        $body = $this->converter->toBody(['test' => '42']);
        $successResponse = new \Zend_Http_Response(201, [$this->converter->getContentTypeHeader()], $body);
        $this->updateResponseResolver->getResult($successResponse);
        $this->assertCronWasNotSet();
    }

    /**
     * @magentoDataFixture Magento/Analytics/_files/enabled_subscription_with_invalid_token.php
     * @magentoDbIsolation enabled
     */
    public function testReSignUpOnNotifyDataChangedWasNotCalledWhenSubscriptionUpdateIsRunning()
    {
        $this->flagManager
            ->saveFlag(
                SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE,
                'https://previous.example.com/'
            );
        $body = $this->converter->toBody(['test' => '42']);
        $retryResponse = new \Zend_Http_Response(401, [$this->converter->getContentTypeHeader()], $body);
        $this->notifyDataChangedResponseResolver->getResult($retryResponse);
        $this->assertCronWasNotSet();
    }

    /**
     * @return string|null
     */
    private function getSubscribeSchedule()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /**
         * @var $scopeConfig ScopeConfigInterface
         */
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);

        return $scopeConfig->getValue(
            SubscriptionHandler::CRON_STRING_PATH,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }

    /**
     * @return int|null
     */
    private function getAttemptFlag()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var $flagManager FlagManager
         */
        $flagManager = $objectManager->get(FlagManager::class);

        return $flagManager->getFlagData(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
    }

    /**
     * @return void
     */
    private function assertCronWasSet()
    {
        $this->assertEquals('0 * * * *', $this->getSubscribeSchedule());
        $this->assertGreaterThan(1, $this->getAttemptFlag());
    }

    /**
     * @return void
     */
    private function assertCronWasNotSet()
    {
        $this->assertNull($this->getSubscribeSchedule());
        $this->assertNull($this->getAttemptFlag());
    }
}
