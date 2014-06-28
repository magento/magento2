<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Config;

/**
 * Tax Config Notification
 */
class Notification extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Core\Model\Resource\Config
     */
    protected $resourceConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Core\Model\Resource\Config $resourceConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Core\Model\Resource\Config $resourceConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->resourceConfig = $resourceConfig;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare and store cron settings after save
     *
     * @return \Magento\Tax\Model\Config\Notification
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_resetNotificationFlag(\Magento\Tax\Model\Config::XML_PATH_TAX_NOTIFICATION_IGNORE_DISCOUNT);
            $this->_resetNotificationFlag(\Magento\Tax\Model\Config::XML_PATH_TAX_NOTIFICATION_IGNORE_PRICE_DISPLAY);
        }
        return parent::_afterSave($this);
    }

    /**
     * Reset flag for showing tax notifications
     *
     * @param string $path
     * @return \Magento\Tax\Model\Config\Notification
     */
    protected function _resetNotificationFlag($path)
    {
        $this->resourceConfig->saveConfig($path, 0, \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, 0);
        return $this;
    }
}
