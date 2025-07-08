<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlNewRelic\Model\Query\Logger;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\GraphQl\Model\Query\Logger\LoggerInterface;

/**
 * Logs GraphQl query data for New Relic
 */
class NewRelic implements LoggerInterface
{
    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        private Config $config,
        private NewRelicWrapper $newRelicWrapper
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(array $queryDetails)
    {
        $transactionName = $queryDetails[LoggerInterface::TOP_LEVEL_OPERATION_NAME] ?? '';
        $this->newRelicWrapper->setTransactionName('GraphQL-' . $transactionName);
        if (!$this->config->isNewRelicEnabled()) {
            return;
        }
        foreach ($queryDetails as $key => $value) {
            $this->newRelicWrapper->addCustomParameter($key, $value);
        }
    }
}
