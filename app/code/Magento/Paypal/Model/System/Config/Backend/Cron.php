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
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model\System\Config\Backend;

class Cron extends \Magento\Core\Model\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/paypal_fetch_settlement_reports/schedule/cron_expr';

    const CRON_MODEL_PATH_INTERVAL = 'paypal/fetch_reports/schedule';

    /**
     * @var \Magento\Core\Model\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\Core\Model\Config\ValueFactory $configValueFactory
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\ConfigInterface $config,
        \Magento\Core\Model\Config\ValueFactory $configValueFactory,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Cron settings after save
     *
     * @return $this
     */
    protected function _afterSave()
    {
        $cronExprString = '';
        $time = explode(
            ',',
            $this->_configValueFactory->create()->load('paypal/fetch_reports/time', 'path')->getValue()
        );

        if ($this->_configValueFactory->create()->load('paypal/fetch_reports/active', 'path')->getValue()) {
            $interval = $this->_configValueFactory->create()->load(self::CRON_MODEL_PATH_INTERVAL, 'path')->getValue();
            $cronExprString = "{$time[1]} {$time[0]} */{$interval} * *";
        }

        $this->_configValueFactory->create()->load(
            self::CRON_STRING_PATH,
            'path'
        )->setValue(
            $cronExprString
        )->setPath(
            self::CRON_STRING_PATH
        )->save();

        return parent::_afterSave();
    }
}
