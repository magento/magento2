<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Config value backend model.
 * @since 2.2.0
 */
class CollectionTime extends Value
{
    /**
     * The path to config setting of schedule of collection data cron.
     */
    const CRON_SCHEDULE_PATH = 'crontab/default/jobs/analytics_collect_data/schedule/cron_expr';

    /**
     * @var WriterInterface
     * @since 2.2.0
     */
    private $configWriter;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param WriterInterface $configWriter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        WriterInterface $configWriter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configWriter = $configWriter;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}. Set schedule setting for cron.
     *
     * @return Value
     * @since 2.2.0
     */
    public function afterSave()
    {
        $result = preg_match('#(?<hour>\d{2}),(?<min>\d{2}),(?<sec>\d{2})#', $this->getValue(), $time);

        if (!$result) {
            throw new LocalizedException(__('Time value has an unsupported format'));
        }

        $cronExprArray = [
            $time['min'],           # Minute
            $time['hour'],          # Hour
            '*',                    # Day of the Month
            '*',                    # Month of the Year
            '*',                    # Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->configWriter->save(self::CRON_SCHEDULE_PATH, $cronExprString);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            throw new LocalizedException(__('Cron settings can\'t be saved'));
        }

        return parent::afterSave();
    }
}
