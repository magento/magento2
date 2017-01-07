<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Tax\Model\Config;

/**
 * Tax Config Notification
 */
class Notification extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceConfig = $resourceConfig;
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
            $this->_resetNotificationFlag(Config::XML_PATH_TAX_NOTIFICATION_IGNORE_PRICE_EXCLUDING_TAX_SETTINGS);
        }
        return parent::afterSave();
    }

    /**
     * Reset flag for showing tax notifications
     *
     * @param string $path
     * @return \Magento\Tax\Model\Config\Notification
     */
    protected function _resetNotificationFlag($path)
    {
        $this->resourceConfig->saveConfig($path, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }
}
