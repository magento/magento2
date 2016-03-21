<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Constructor
     *
     * @param Config $config
     * @param Collect $collect
     * @param \Magento\NewRelicReporting\Model\SystemFactory $systemFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        Config $config,
        Collect $collect,
        \Magento\NewRelicReporting\Model\SystemFactory $systemFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->config = $config;
        $this->collect = $collect;
        $this->systemFactory = $systemFactory;
        $this->jsonEncoder = $jsonEncoder;
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
                            ];
                            break;
                        case Config::DISABLED:
                            $modelData = [
                                'type' => Config::MODULE_DISABLED,
                                'action' => $this->jsonEncoder->encode($change),
                            ];
                            break;
                        case Config::INSTALLED:
                            $modelData = [
                                'type' => Config::MODULE_INSTALLED,
                                'action' => $this->jsonEncoder->encode($change),
                            ];
                            break;
                        case Config::UNINSTALLED:
                            $modelData = [
                                'type' => Config::MODULE_UNINSTALLED,
                                'action' => $this->jsonEncoder->encode($change),
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
