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
use Magento\Search\Model\Query;

/**
 * Order filter allows to filter collection using 'id, url_key, name' from search criteria.
 */
class OrderFilter
{
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
                    $this->addAttributeFilter($orderCollection, $field, $condType, $value, $store);
                }
            }
        }
    }

    /**
     * Add filter to order collection
     *
     * @param Collection $orderCollection
     * @param string $field
     * @param string $condType
     * @param string|array $value
     * @param StoreInterface $store
     * @throws InputException
     */
    private function addAttributeFilter($orderCollection, $field, $condType, $value, $store): void
    {
        if ($condType === 'match') {
            $this->addMatchFilter($orderCollection, $field, $value, $store);
            return;
        }
        $orderCollection->addAttributeToFilter($field, [$condType => $value]);
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
    private function addMatchFilter($orderCollection, $field, $value, $store): void
    {
        $minQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $searchValue = str_replace('%', '', $value);
        $matchLength = strlen($searchValue);
        if ($matchLength < $minQueryLength) {
            throw new InputException(__('Invalid match filter'));
        }

        $orderCollection->addAttributeToFilter($field, ['like' => "%{$searchValue}%"]);
    }
}
