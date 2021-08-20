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
     * @var array
     */
    private $queryDetails;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
     * @param array $queryDetails
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        array $queryDetails,
        Config $config,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->queryDetails = $queryDetails;
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->config->isNewRelicEnabled()) {
            return;
        }

        foreach ($this->queryDetails as $key => $value) {
            $this->newRelicWrapper->addCustomParameter($key, $value);
        }

        $transactionName = $this->queryDetails[LoggerInterface::QUERY_NAMES] ?: '';
        if (strpos($this->queryDetails[LoggerInterface::QUERY_NAMES], ',') > 0) {
            $transactionName = 'multipleQueries';
        }
        $this->newRelicWrapper->setTransactionName('GraphQL-' . $transactionName);
    }

}
