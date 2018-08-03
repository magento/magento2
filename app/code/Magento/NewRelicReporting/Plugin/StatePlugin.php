<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Plugin;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Psr\Log\LoggerInterface;

class StatePlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
        $this->logger = $logger;
    }

    /**
     * Set separate appname
     *
     * @param State $subject
     * @param null $result
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetAreaCode(State $state, $result)
    {
        if (!$this->shouldSetAppName()) {
            return $result;
        }

        try {
            $this->newRelicWrapper->setAppName($this->appName($state));
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $result;
        }
    }

    /**
     * @param State $state
     *
     * @return string
     * @throws LocalizedException
     */
    private function appName(State $state)
    {
        $code = $state->getAreaCode();
        $current = $this->config->getNewRelicAppName();

        return $current . ';' . $current . '_' . $code;
    }

    /**
     * @return bool
     */
    private function shouldSetAppName()
    {
        if (!$this->config->isNewRelicEnabled()) {
            return false;
        }

        if (!$this->config->getNewRelicAppName()) {
            return false;
        }

        if (!$this->config->isSeparateApps()) {
            return false;
        }

        return true;
    }
}
