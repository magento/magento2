<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\SearchCriteria;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter as UiFulltextFilter;

class FulltextFilterPlugin
{

    /**
     * Use LIKE instead of MATCH AGAINST in sales order grid to bypass MySQL stopword limitations
     *
     * @param UiFulltextFilter $subject
     * @param \Closure $proceed
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApply(
        UiFulltextFilter $subject,
        \Closure $proceed,
        Collection $collection,
        Filter $filter
    ): void {
        if ($collection instanceof OrderGridCollection) {
            $raw = trim((string) $filter->getValue());
            if ($raw === '') {
                return;
            }

            $normalized = preg_replace('/^\{+|\}+$/', '', $raw);
            $normalized = ltrim($normalized);
            $normalized = ltrim($normalized, '#');

            // Exact increment_id search when normalized is all digits
            if ($normalized !== '' && ctype_digit($normalized)) {
                $collection->addFieldToFilter('increment_id', ['eq' => $normalized]);
                return;
            }

            // LIKE across key columns (for names, emails, non-digit terms)
            $valueForLike = trim($raw, '{}');
            $valueForLike = ltrim($valueForLike);
            $valueForLike = ltrim($valueForLike, '#');

            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $valueForLike) . '%';

            $fields = ['increment_id', 'billing_name', 'shipping_name', 'customer_email'];
            $conditions = array_fill(0, count($fields), ['like' => $like]);

            $collection->addFieldToFilter($fields, $conditions);
            return;
        }
        $proceed($collection, $filter);
    }
}
