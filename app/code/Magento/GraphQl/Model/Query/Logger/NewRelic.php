<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Query\Logger;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Logs GraphQl query data for New Relic
 */
class NewRelic implements LoggerInterface
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
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $queryDetails)
    {
        if (!$this->config->isNewRelicEnabled()) {
            return;
        }

        foreach ($queryDetails as $key => $value) {
            $this->newRelicWrapper->addCustomParameter($key, $value);
        }

        $transactionName = $queryDetails[LoggerInterface::OPERATION_NAMES] ?: '';
        if (strpos($queryDetails[LoggerInterface::OPERATION_NAMES], ',') !== false) {
            $transactionName = 'multipleQueries';
        }
        $this->newRelicWrapper->setTransactionName('GraphQL-' . $transactionName);
    }
}
