<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\Framework\Message\ManagerInterface;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Class CheckConfig
 * @since 2.0.0
 */
class CheckConfig implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var NewRelicWrapper
     * @since 2.0.0
     */
    protected $newRelicWrapper;

    /**
     * @var ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     * @param ManagerInterface $messageManager
     * @since 2.0.0
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
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(Observer $observer)
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
    }
}
