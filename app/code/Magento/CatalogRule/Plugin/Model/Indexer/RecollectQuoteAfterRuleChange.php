<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Plugin\Model\Indexer;

use Magento\Catalog\Model\Indexer\Product\Price;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;

/**
 * Recollect quote on product ids after rule change.
 */
class RecollectQuoteAfterRuleChange
{
    /**
     * @param QuoteResourceModel $quoteResourceModel
     */
    public function __construct(
        private readonly QuoteResourceModel $quoteResourceModel
    ) {
    }

    /**
     * Recollect quote on product ids after rule change.
     *
     * @param Price $subject
     * @param void $result
     * @param array $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Price $subject, $result, array $ids)
    {
        $this->quoteResourceModel->markQuotesRecollect($ids);
    }

    /**
     * Recollect quote on product ids after rule change applied in the "Update on Save" indexer mode.
     *
     * @param Price $subject
     * @param void $result
     * @param array $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(Price $subject, $result, array $ids)
    {
        $this->quoteResourceModel->markQuotesRecollect($ids);
    }

    /**
     * Recollect quote on product id after rule change applied in the "Update on Save" indexer mode.
     *
     * @param Price $subject
     * @param void $result
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteRow(Price $subject, $result, $id)
    {
        $this->quoteResourceModel->markQuotesRecollect([$id]);
    }
}
