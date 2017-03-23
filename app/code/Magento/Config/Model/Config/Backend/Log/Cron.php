<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Log Cron Backend Model
 */
namespace Magento\Config\Model\Config\Backend\Log;

class Cron extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/log_clean/schedule/cron_expr';

    const CRON_MODEL_PATH = 'crontab/default/jobs/log_clean/run/model';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->_runModelPath = $runModelPath;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Cron settings after save
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $enabled = $this->getData('groups/log/fields/enabled/value');
        $time = $this->getData('groups/log/fields/time/value');
        $frequency = $this->getData('groups/log/fields/frequency/value');

        $frequencyWeekly = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequencyMonthly = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronExprArray = [
                intval($time[1]),                                 # Minute
                intval($time[0]),                                 # Hour
                $frequency == $frequencyMonthly ? '1' : '*',      # Day of the Month
                '*',                                              # Month of the Year
                $frequency == $frequencyWeekly ? '1' : '*',        # Day of the Week
            ];
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            /** @var $configValue \Magento\Framework\App\Config\ValueInterface */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_STRING_PATH, 'path');
            $configValue->setValue($cronExprString)->setPath(self::CRON_STRING_PATH)->save();

            /** @var $configValue \Magento\Framework\App\Config\ValueInterface */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_MODEL_PATH, 'path');
            $configValue->setValue($this->_runModelPath)->setPath(self::CRON_MODEL_PATH)->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the Cron expression.'));
        }
        return parent::afterSave();
    }
}
