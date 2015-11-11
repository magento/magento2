<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Cron;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Module\Collect;

/**
 * Class ReportModulesInfo
 */
class ReportModulesInfo
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Collect
     */
    protected $collect;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory
     */
    protected $systemFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param Config $config
     * @param Collect $collect
     * @param \Magento\NewRelicReporting\Model\SystemFactory $systemFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        Config $config,
        Collect $collect,
        \Magento\NewRelicReporting\Model\SystemFactory $systemFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->config = $config;
        $this->collect = $collect;
        $this->systemFactory = $systemFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->dateTime = $dateTime;
    }

    /**
     * Reports Modules and module changes to the database reporting_module_status table
     *
     * @return \Magento\NewRelicReporting\Model\Cron\ReportModulesInfo
     */
    public function report()
    {
        if ($this->config->isNewRelicEnabled()) {
            $moduleData = $this->collect->getModuleData();
            if (count($moduleData['changes']) > 0) {
                foreach ($moduleData['changes'] as $change) {
                    switch ($change['type']) {
                        case Config::ENABLED:
                            $modelData = [
                                'type' => Config::MODULE_ENABLED,
                                'action' => $this->jsonEncoder->encode($change),
                                'updated_at' => $this->dateTime->formatDate(true)
                            ];
                            break;
                        case Config::DISABLED:
                            $modelData = [
                                'type' => Config::MODULE_DISABLED,
                                'action' => $this->jsonEncoder->encode($change),
                                'updated_at' => $this->dateTime->formatDate(true)
                            ];
                            break;
                        case Config::INSTALLED:
                            $modelData = [
                                'type' => Config::MODULE_INSTALLED,
                                'action' => $this->jsonEncoder->encode($change),
                                'updated_at' => $this->dateTime->formatDate(true)
                            ];
                            break;
                        case Config::UNINSTALLED:
                            $modelData = [
                                'type' => Config::MODULE_UNINSTALLED,
                                'action' => $this->jsonEncoder->encode($change),
                                'updated_at' => $this->dateTime->formatDate(true)
                            ];
                            break;
                    }
                    /** @var \Magento\NewRelicReporting\Model\System $systemModel */
                    $systemModel = $this->systemFactory->create();
                    $systemModel->setData($modelData);
                    $systemModel->save();
                }
            }
        }

        return $this;
    }
}
