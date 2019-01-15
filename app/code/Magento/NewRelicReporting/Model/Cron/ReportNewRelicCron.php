<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Cron;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Module\Collect;
use Magento\NewRelicReporting\Model\Counter;
use Magento\NewRelicReporting\Model\CronEventFactory;
use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;

/**
 * Class ReportNewRelicCron
 */
class ReportNewRelicCron
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
     * @var Counter
     */
    protected $counter;

    /**
     * @var CronEventFactory
     */
    protected $cronEventFactory;

    /**
     * @var DeploymentsFactory
     */
    protected $deploymentsFactory;

    /**
     * Parameters to be sent to Insights
     * @var array
     */
    protected $customParameters = [];

    /**
     * Constructor
     *
     * @param Config $config
     * @param Collect $collect
     * @param Counter $counter
     * @param CronEventFactory $cronEventFactory
     * @param DeploymentsFactory $deploymentsFactory
     */
    public function __construct(
        Config $config,
        Collect $collect,
        Counter $counter,
        CronEventFactory $cronEventFactory,
        DeploymentsFactory $deploymentsFactory
    ) {
        $this->config = $config;
        $this->collect = $collect;
        $this->counter = $counter;
        $this->cronEventFactory = $cronEventFactory;
        $this->deploymentsFactory = $deploymentsFactory;
    }

    /**
     * Queue up custom parameters to send in API call to Insights Events
     *
     * @param array $data
     * @return void
     */
    public function addCustomParameters(array $data)
    {
        foreach ($data as $key => $value) {
            $this->customParameters[$key] = $value;
        }
    }

    /**
     *  Reports current total module counts to Insights
     *
     * @return void
     */
    protected function reportModules()
    {
        $moduleData = $this->collect->getModuleData(false);
        $moduleDataChanges = $moduleData['changes'];
        if (count($moduleDataChanges) > 0) {
            $enabledChangeArray = [];
            $disabledChangeArray = [];
            $installedChangeArray = [];
            $uninstalledChangeArray = [];
            foreach ($moduleDataChanges as $change) {
                switch ($change['type']) {
                    case Config::ENABLED:
                        $enabledChangeArray[] = $change['name'] . '-' . $change['setup_version'];
                        break;
                    case Config::DISABLED:
                        $disabledChangeArray[] = $change['name'] . '-' . $change['setup_version'];
                        break;
                    case Config::INSTALLED:
                        $installedChangeArray[] = $change['name'] . '-' . $change['setup_version'];
                        break;
                    case Config::UNINSTALLED:
                        $uninstalledChangeArray[] = $change['name'] . '-' . $change['setup_version'];
                        break;
                }
            }
            $this->setModuleChangeStatusDeployment($enabledChangeArray, 'Modules Enabled');
            $this->setModuleChangeStatusDeployment($disabledChangeArray, 'Modules Disabled');
            $this->setModuleChangeStatusDeployment($installedChangeArray, 'Modules Installed');
            $this->setModuleChangeStatusDeployment($uninstalledChangeArray, 'Modules Uninstalled');
        }
        $this->addCustomParameters([Config::MODULES_ENABLED => $moduleData[Config::ENABLED]]);
        $this->addCustomParameters([Config::MODULES_DISABLED => $moduleData[Config::DISABLED]]);
        $this->addCustomParameters([Config::MODULES_INSTALLED => $moduleData[Config::INSTALLED]]);
    }

    /**
     * Reports current module change status via deployment marker
     *
     * @param array $changesArray
     * @param string $deploymentText
     * @return void
     */
    protected function setModuleChangeStatusDeployment(array $changesArray, $deploymentText = '')
    {
        if (count($changesArray) > 0) {
            foreach ($changesArray as $change) {
                $this->deploymentsFactory->create()->setDeployment(
                    $deploymentText,
                    $change,
                    'cron'
                );
            }
        }
    }

    /**
     * Reports counts info to New Relic
     *
     * @return void
     * @throws \Exception
     */
    protected function reportCounts()
    {
        $this->addCustomParameters([
            Config::PRODUCT_COUNT => $this->counter->getAllProductsCount(),
            Config::CONFIGURABLE_COUNT => $this->counter->getConfigurableCount(),
            Config::ACTIVE_COUNT => $this->counter->getActiveCatalogSize(),
            Config::CATEGORY_COUNT => $this->counter->getCategoryCount(),
            Config::WEBSITE_COUNT => $this->counter->getWebsiteCount(),
            Config::STORE_VIEW_COUNT => $this->counter->getStoreViewsCount(),
            Config::CUSTOMER_COUNT => $this->counter->getCustomerCount(),
        ]);
        if (!empty($this->customParameters)) {
            $this->cronEventFactory->create()
                ->addData($this->customParameters)
                ->sendRequest();
        }
    }

    /**
     * Reports info to New Relic by Cron
     *
     * @return \Magento\NewRelicReporting\Model\Cron\ReportCounts
     */
    public function report()
    {
        if ($this->config->isNewRelicEnabled()) {
            $this->reportCounts();
        }

        return $this;
    }
}
