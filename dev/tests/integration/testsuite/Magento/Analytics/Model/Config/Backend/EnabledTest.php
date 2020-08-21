<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Analytics\Model\Config\Backend;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigDataResource;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EnabledTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @var ConfigDataResource
     */
    private $configValueResourceModel;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->subscriptionStatusProvider = $this->objectManager->get(SubscriptionStatusProvider::class);
        $this->preparedValueFactory = $this->objectManager->get(PreparedValueFactory::class);
        $this->configValueResourceModel = $this->objectManager->get(ConfigDataResource::class);
        $this->reinitableConfig = $this->objectManager->get(ReinitableConfigInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDisable()
    {
        $this->checkInitialStatus();
        $this->saveConfigValue(Enabled::XML_ENABLED_CONFIG_STRUCTURE_PATH, (string)Enabledisable::DISABLE_VALUE);
        $this->reinitableConfig->reinit();

        $this->checkDisabledStatus();
    }

    /**
     * @depends testDisable
     * @magentoDbIsolation enabled
     */
    public function testReEnable()
    {
        $this->checkDisabledStatus();
        $this->saveConfigValue(Enabled::XML_ENABLED_CONFIG_STRUCTURE_PATH, (string)Enabledisable::ENABLE_VALUE);
        $this->checkReEnabledStatus();
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
     * Check the instance status after installation
     */
    private function checkInitialStatus()
    {
        $this->assertNotSame(SubscriptionStatusProvider::DISABLED, $this->subscriptionStatusProvider->getStatus());
        $this->assertNotEmpty($this->getConfigValue(CollectionTime::CRON_SCHEDULE_PATH));
    }

    /**
     * Check the instance status after disabling AR synchronisation
     */
    private function checkDisabledStatus()
    {
        $this->assertSame(SubscriptionStatusProvider::DISABLED, $this->subscriptionStatusProvider->getStatus());
        $this->assertEmpty($this->getConfigValue(CollectionTime::CRON_SCHEDULE_PATH));
    }

    /**
     * Check the instance status after re-activation AR synchronisation
     */
    private function checkReEnabledStatus()
    {
        $this->assertContains(
            $this->subscriptionStatusProvider->getStatus(),
            [
                SubscriptionStatusProvider::ENABLED,
                SubscriptionStatusProvider::PENDING,
            ]
        );
        $this->assertNotEmpty($this->getConfigValue(CollectionTime::CRON_SCHEDULE_PATH));
    }
}
