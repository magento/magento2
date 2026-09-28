<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteMutexInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;

class LoadQuoteByIdMutex implements QuoteMutexInterface
{
    /**
     * @param QuoteResourceModel $quoteResourceModel
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        private readonly QuoteResourceModel $quoteResourceModel,
        private readonly QuoteFactory $quoteFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(array $maskedIds, callable $callable, array $args = [])
    {
        if (empty($maskedIds)) {
            throw new \InvalidArgumentException('Quote ids must be provided');
        }

        $connection = $this->quoteResourceModel->getConnection();
        $connection->beginTransaction();
        $query = $connection->select()
            ->from($this->quoteResourceModel->getMainTable())
            ->where($connection->prepareSqlCondition($this->quoteResourceModel->getIdFieldName(), ['in' => $maskedIds]))
            ->forUpdate(true);
        $rows = $connection->fetchAll($query);
        $quotes = [];
        foreach ($rows as $data) {
            $quote = $this->quoteFactory->create();
            $quote->setData($data);
            $quote->setOrigData();
            $quotes[] = $quote;
        }
        $args[] = $quotes;
        try {
            $result = $callable(...$args);
            $connection->commit();
            return $result;
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
