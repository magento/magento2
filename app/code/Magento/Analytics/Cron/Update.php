<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class Update
 * Executes by cron schedule in case base url was changed
 */
class Update
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * Reinitable Config Model.
     *
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * Update constructor.
     * @param Connector $connector
     * @param WriterInterface $configWriter
     * @param ReinitableConfigInterface $reinitableConfig
     * @param FlagManager $flagManager
     */
    public function __construct(
        Connector $connector,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        FlagManager $flagManager
    ) {
        $this->connector = $connector;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->flagManager = $flagManager;
    }

    /**
     * Execute scheduled update operation
     *
     * @return bool
     */
    public function execute()
    {
        $updateResult = $this->connector->execute('update');
        if ($updateResult === false) {
            return false;
        }
        $this->configWriter->delete(BaseUrlConfigPlugin::UPDATE_CRON_STRING_PATH);
        $this->flagManager->deleteFlag(BaseUrlConfigPlugin::OLD_BASE_URL_FLAG_CODE);
        $this->reinitableConfig->reinit();
        return true;
    }
}
