<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config;

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Tax\Model\Config;

/**
 * Tax Config Notification
 */
class Notification extends Value
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'tax_config_notification';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ResourceConfig $resourceConfig
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        protected readonly ResourceConfig $resourceConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare and store cron settings after save
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_resetNotificationFlag(Config::XML_PATH_TAX_NOTIFICATION_IGNORE_DISCOUNT);
            $this->_resetNotificationFlag(Config::XML_PATH_TAX_NOTIFICATION_IGNORE_PRICE_DISPLAY);
            $this->_resetNotificationFlag(Config::XML_PATH_TAX_NOTIFICATION_IGNORE_APPLY_DISCOUNT);
        }
        return parent::afterSave();
    }

    /**
     * Reset flag for showing tax notifications
     *
     * @param string $path
     * @return Notification
     */
    protected function _resetNotificationFlag($path)
    {
        $this->resourceConfig->saveConfig($path, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }
}
