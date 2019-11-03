<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class BaseUrlConfigPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @var ConfigData
     */
    private $configValueResourceModel;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->preparedValueFactory = $this->objectManager->get(PreparedValueFactory::class);
        $this->configValueResourceModel = $this->objectManager->get(ConfigData::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->flagManager = $this->objectManager->get(FlagManager::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testAfterSaveNotSecureUrl()
    {
        $this->saveConfigValue(
            Store::XML_PATH_UNSECURE_BASE_URL,
            'http://store.com/',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->assertCronWasNotSet();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testAfterSaveSecureUrlNotInDefaultScope()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'https://store.com/',
            ScopeInterface::SCOPE_STORES
        );
        $this->assertCronWasNotSet();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture web/secure/base_url https://previous.example.com/
     */
    public function testAfterSaveSecureUrlInDefaultScopeOnDoesNotRegisteredInstance()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'https://store.com/',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->assertCronWasNotSet();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture web/secure/base_url https://previous.example.com/
     * @magentoAdminConfigFixture analytics/general/token MBI_token
     */
    public function testAfterSaveSecureUrlInDefaultScopeOnRegisteredInstance()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'https://store.com/',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->assertCronWasSet();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture web/secure/base_url https://previous.example.com/
     * @magentoAdminConfigFixture analytics/general/token MBI_token
     */
    public function testAfterSaveMultipleBaseUrlChanges()
    {
        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'https://store.com/',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        $this->saveConfigValue(
            Store::XML_PATH_SECURE_BASE_URL,
            'https://store10.com/',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->assertCronWasSet();
    }

    /**
     * @param string $path The configuration path in format section/group/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @return void
     */
    private function saveConfigValue(string $path, string $value, string $scope)
    {
        $value = $this->preparedValueFactory->create(
            $path,
            $value,
            $scope
        );
        $this->configValueResourceModel->save($value);
    }

    /**
     * @return void
     */
    private function assertCronWasNotSet()
    {
        $this->assertNull($this->getSubscriptionUpdateSchedule());
        $this->assertNull($this->getPreviousUpdateUrl());
        $this->assertNull($this->getUpdateReverseCounter());
    }

    /**
     * @return void
     */
    private function assertCronWasSet()
    {
        $this->assertSame(
            '0 * * * *',
            $this->getSubscriptionUpdateSchedule(),
            'Subscription update schedule has not been set'
        );
        $this->assertSame(
            'https://previous.example.com/',
            $this->getPreviousUpdateUrl(),
            'The previous URL stored for update is not correct'
        );
        $this->assertSame(48, $this->getUpdateReverseCounter());
    }

    /**
     * @return mixed
     */
    private function getSubscriptionUpdateSchedule()
    {
        return $this->scopeConfig->getValue(
            SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @return mixed
     */
    private function getPreviousUpdateUrl()
    {
        return $this->flagManager->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE);
    }

    /**
     * @return mixed
     */
    private function getUpdateReverseCounter()
    {
        return $this->flagManager
            ->getFlagData(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
    }
}
