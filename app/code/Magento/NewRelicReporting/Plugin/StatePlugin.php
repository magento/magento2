<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Plugin;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Psr\Log\LoggerInterface;

/**
 * Handles setting which, when enabled, reports frontend and adminhtml as separate apps to New Relic.
 */
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
     * @param LoggerInterface $logger
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
     * @param mixed $result
     * @return mixed
     */
    public function afterSetAreaCode(State $subject, $result)
    {
        if (!$this->shouldSetAppName()) {
            return $result;
        }

        try {
            $this->newRelicWrapper->setAppName($this->appName($subject));
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $result;
        }

        return $result;
    }

    /**
     * Format appName.
     *
     * @param State $state
     * @return string
     * @throws LocalizedException
     */
    private function appName(State $state): string
    {
        $code = $state->getAreaCode();
        $current = $this->config->getNewRelicAppName();

        return $current . ';' . $current . '_' . $code;
    }

    /**
     * Check if app name should be set.
     *
     * @return bool
     */
    private function shouldSetAppName(): bool
    {
        return (
            $this->config->isSeparateApps() &&
            $this->config->getNewRelicAppName() &&
            $this->config->isNewRelicEnabled()
        );
    }
}
