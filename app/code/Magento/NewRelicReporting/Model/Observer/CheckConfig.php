<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\Framework\Message\ManagerInterface;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

class CheckConfig implements ObserverInterface
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
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            if (!$this->newRelicWrapper->isExtensionInstalled()) {
                $this->config->disableModule();
                $this->messageManager->addErrorMessage(
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
