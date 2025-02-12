<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend Model for Currency import options
 */
namespace Magento\Config\Model\Config\Backend\Currency;

use Magento\Framework\Exception\LocalizedException;

/**
 * Cron job configuration for currency
 *
 * @api
 * @since 100.0.2
 */
class Cron extends \Magento\Framework\App\Config\Value
{
    public const CRON_STRING_PATH = 'crontab/default/jobs/currency_rates_update/schedule/cron_expr';
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save handler
     *
     * @return $this
     * @throws LocalizedException
     */
    public function afterSave()
    {
        $time = $this->getData('groups/import/fields/time/value');
        if (empty($time)) {
            $time = explode(
                ',',
                $this->_config->getValue(
                    'currency/import/time',
                    $this->getScope(),
                    $this->getScopeId()
                ) ?: '0,0,0'
            );
            $frequency = $this->_config->getValue(
                'currency/import/frequency',
                $this->getScope(),
                $this->getScopeId()
            );
        } else {
            $frequency = $this->getData('groups/import/fields/frequency/value');
        }
        $frequencyWeekly = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequencyMonthly = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

        $cronExprArray = [
            (int)$time[1],                                 # Minute
            (int)$time[0],                                 # Hour
            $frequency == $frequencyMonthly ? '1' : '*',      # Day of the Month
            '*',                                              # Month of the Year
            $frequency == $frequencyWeekly ? '1' : '*',        # Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            /** @var $configValue \Magento\Framework\App\Config\ValueInterface */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_STRING_PATH, 'path');
            $configValue->setValue($cronExprString)->setPath(self::CRON_STRING_PATH)->save();
        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t save the Cron expression.'));
        }
        return parent::afterSave();
    }
}
