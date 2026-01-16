<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Plugin;

use GraphQL\Language\AST\DocumentNode;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema;
use Magento\GraphQlNewRelic\Helper\NewRelicReportData;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Plugin that sets GraphQL transaction names for New Relic.
 */
class TransactionNaming
{
    /**
     * @param NewRelicWrapper $newRelicWrapper
     * @param NewRelicReportData $reportData
     */
    public function __construct(
        private NewRelicWrapper $newRelicWrapper,
        private NewRelicReportData $reportData
    ) {
    }

    /**
     * Rename a GraphQl transaction for New Relic before processing it.
     *
     * @param QueryProcessor $subject
     * @param Schema $schema
     * @param DocumentNode|string $source
     * @param ContextInterface|null $contextValue
     * @param array|null $variableValues
     * @param string|null $operationName
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(
        QueryProcessor $subject,
        Schema $schema,
        DocumentNode|string $source,
        ?ContextInterface $contextValue = null,
        ?array $variableValues = null,
        ?string $operationName = null
    ): void {
        $transactionData = $this->reportData->getTransactionData($schema, $source);
        if (empty($transactionData)) {
            return;
        }

        $this->newRelicWrapper->setTransactionName($transactionData['transactionName']);
        $this->newRelicWrapper->addCustomParameter('GraphqlNumberOfFields', $transactionData['fieldCount']);
        $this->newRelicWrapper->addCustomParameter('FieldNames', implode('|', $transactionData['fieldNames']));
    }
}
