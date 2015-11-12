<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;
use Magento\Framework\Message\ManagerInterface;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Class CheckConfig
 */
class CheckConfig
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var NewRelicWrapper
     */
    protected $newRelicWrapper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor
     *
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper,
        ManagerInterface $messageManager
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
        $this->messageManager = $messageManager;
    }

    /**
     * Update items stock status and low stock date.
     *
     * @return \Magento\NewRelicReporting\Model\Observer\ReportConcurrentAdminsToNewRelic
     */
    public function execute()
    {
        if ($this->config->isNewRelicEnabled()) {
            if (!$this->newRelicWrapper->isExtensionInstalled()) {
                $this->config->disableModule();
                $this->messageManager->addError(
                    __(
                        'The New Relic integration requires the newrelic-php5 agent, which is not installed. More 
                        information on installing the agent is available <a target="_blank" href="%1">here</a>.',
                        'https://docs.newrelic.com/docs/agents/php-agent/installation/php-agent-installation-overview'
                    ),
                    $this->messageManager->getDefaultGroup()
                );
            }
        }
        return $this;
    }
}
