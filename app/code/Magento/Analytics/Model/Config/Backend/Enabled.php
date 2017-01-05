<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\Config\Backend;


use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Enabled extends Value
{
    /**
     * Flag code for reserve counter of attempts to subscribe.
     */
    const ATTEMPTS_REVERSE_COUNTER_FLAG_CODE = 'analytics_link_attempts_reverse_counter';

    /**
     * Config path for schedule setting of subscription handler.
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/analytics_generate/schedule/cron_expr';

    /**
     * Max value for reserve counter of attempts to subscribe.
     *
     * @var int
     */
    private $attemptsInitValue = 24;

    /**
     * Config value factory.
     *
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * Flag factory.
     *
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * Flag resource model.
     *
     * @var FlagResource
     */
    private $flagResource;

    /**
     * Resource model for config values.
     *
     * @var Data
     */
    private $configValueResource;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param FlagFactory $flagFactory
     * @param FlagResource $flagResource
     * @param Data $configValueResource
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        FlagFactory $flagFactory,
        FlagResource $flagResource,
        Data $configValueResource,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
        $this->configValueResource = $configValueResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Add additional handling after config value was saved.
     *
     * @return Value
     * @throws LocalizedException
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {

            $enabled = $this->getData('value');

            if ($enabled) {
                try {
                    $this->setCronSchedule();
                    $this->setAttemptsFlag();
                } catch (\Exception $e) {
                    throw new LocalizedException(__('We can\'t save the Cron expression.'));
                }
            }
        }

        return parent::afterSave();
    }

    /**
     * Set cron schedule setting into config for activation of subscription process.
     *
     * @return bool
     */
    private function setCronSchedule()
    {
        $cronExprArray = [
            '0',                    # Minute
            '*',                    # Hour
            '*',                    # Day of the Month
            '*',                    # Month of the Year
            '*',                    # Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        /** @var Value $configValue */
        $configValue = $this->configValueFactory->create();
        $this->configValueResource->load($configValue, self::CRON_STRING_PATH, 'path');

        $configValue->setValue($cronExprString);
        $configValue->setPath(self::CRON_STRING_PATH);
        $this->configValueResource->save($configValue);

        return true;
    }

    /**
     * Set flag as reserve counter of attempts subscription operation.
     *
     * @return bool
     */
    private function setAttemptsFlag()
    {
        $attemptsFlag = $this->flagFactory
            ->create(['data' => ['flag_code' => self::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE]])
            ->loadSelf();
        $attemptsFlag->setFlagData($this->attemptsInitValue);

        $this->flagResource->save($attemptsFlag);

        return true;
    }
}
