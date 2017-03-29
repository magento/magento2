<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\SearchInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory as ConfigValueFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model for handling of changing of subscription status to Magento BI.
 */
class Subscription
{
    /**
     * Path to field subscription enabled into config structure.
     *
     * @var string
     */
    private $enabledConfigStructurePath = 'analytics/general/enabled';

    /**
     * Value which equal Yes for Yesno dropdown.
     *
     * @var int
     */
    private $yesValueDropdown = 1;

    /**
     * Resource for storing store configuration values.
     *
     * @var ConfigValueFactory
     */
    private $configValueFactory;

    /**
     * Config structure object which allow get field into config by path.
     *
     * @var SearchInterface
     */
    private $configStructure;

    /**
     * Resource model for config values.
     *
     * @var AbstractDb
     */
    private $configValueResource;

    /**
     * Reinitable Config Model.
     *
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * Service for processing of activation/deactivation MBI subscription.
     *
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * Resource which provides a status of subscription.
     *
     * @var SubscriptionStatusProvider
     */
    private $statusProvider;

    /**
     * @param ConfigValueFactory $configValueFactory
     * @param SearchInterface $configStructure
     * @param AbstractDb $configValueResource
     * @param ReinitableConfigInterface $reinitableConfig
     * @param SubscriptionHandler $subscriptionHandler
     * @param SubscriptionStatusProvider $statusProvider
     */
    public function __construct(
        ConfigValueFactory $configValueFactory,
        SearchInterface $configStructure,
        AbstractDb $configValueResource,
        ReinitableConfigInterface $reinitableConfig,
        SubscriptionHandler $subscriptionHandler,
        SubscriptionStatusProvider $statusProvider
    ) {
        $this->configValueFactory = $configValueFactory;
        $this->configStructure = $configStructure;
        $this->configValueResource = $configValueResource;
        $this->reinitableConfig = $reinitableConfig;
        $this->subscriptionHandler = $subscriptionHandler;
        $this->statusProvider = $statusProvider;
    }

    /**
     * Set subscription enabled config value.
     *
     * @return boolean
     */
    public function enable()
    {
        /** @var Field $field */
        $field = $this->configStructure->getElement($this->enabledConfigStructurePath);
        /** @var Value $configValue */
        $configValue = $field->hasBackendModel()
            ? $field->getBackendModel()
            : $this->configValueFactory->create();
        $configPath = $field->getConfigPath() ?: $this->enabledConfigStructurePath;

        $this->configValueResource
            ->load($configValue, $configPath, 'path');

        $configValue->setValue($this->yesValueDropdown);
        $configValue->setPath($configPath);

        $this->configValueResource
            ->save($configValue);

        $this->reinitableConfig->reinit();

        return true;
    }

    /**
     * Retry process of subscription that was unsuccessful.
     *
     * @return bool
     */
    public function retry()
    {
        if ($this->statusProvider->getStatus() === SubscriptionStatusProvider::FAILED) {
            $this->subscriptionHandler->processEnabled();
            $this->reinitableConfig->reinit();
        }

        return true;
    }
}
