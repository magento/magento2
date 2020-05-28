<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query;

use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Order filter allows to filter collection using 'increment_id' as order number, from the search criteria.
 */
class OrderFilter
{
    /** Minimum query lenth for the filter */
    private const DEFAULT_MIN_QUERY_LENGTH = 3;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Translator field from graphql to collection field
     *
     * @var string[]
     */
    private $fieldTranslatorArray = [
        'number' => 'increment_id',
    ];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string[] $fieldTranslatorArray
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $fieldTranslatorArray = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->fieldTranslatorArray = array_replace($this->fieldTranslatorArray, $fieldTranslatorArray);
    }

    /**
     * Filter for filtering the requested categories id's based on url_key, ids, name in the result.
     *
     * @param array $args
     * @param Collection $orderCollection
     * @param StoreInterface $store
     * @throws InputException
     */
    public function applyFilter(array $args, Collection $orderCollection, StoreInterface $store): void
    {
        if (isset($args['filter'])) {
            foreach ($args['filter'] as $field => $cond) {
                if (isset($this->fieldTranslatorArray[$field])) {
                    $field = $this->fieldTranslatorArray[$field];
                }
                foreach ($cond as $condType => $value) {
                    if ($condType === 'match') {
                        if (is_array($value)) {
                            throw new InputException(__('Invalid match filter'));
                        }
                        $this->addMatchFilter($orderCollection, $field, $value, $store);
                        return;
                    }
                    $orderCollection->addAttributeToFilter($field, [$condType => $value]);
                }
            }
        }
    }

    /**
     * Add match filter to collection
     *
     * @param Collection $orderCollection
     * @param string $field
     * @param string $value
     * @param StoreInterface $store
     * @throws InputException
     */
    private function addMatchFilter(
        Collection $orderCollection,
        string $field,
        string $value,
        StoreInterface $store
    ): void {
        $minQueryLength = $this->scopeConfig->getValue(
            'catalog/search/min_query_length',
            ScopeInterface::SCOPE_STORE,
            $store
        ) ?? self::DEFAULT_MIN_QUERY_LENGTH;
        $searchValue = str_replace('%', '', $value);
        $matchLength = strlen($searchValue);
        if ($matchLength < $minQueryLength) {
            throw new InputException(__('Invalid match filter. Minimum length is %1.', $minQueryLength));
        }
        $orderCollection->addAttributeToFilter($field, ['like' => "%{$searchValue}%"]);
    }
}
