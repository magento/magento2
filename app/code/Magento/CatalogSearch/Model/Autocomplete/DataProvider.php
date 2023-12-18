<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Autocomplete;

use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * Catalog search auto-complete data provider.
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Autocomplete limit
     */
    public const CONFIG_AUTOCOMPLETE_LIMIT = 'catalog/search/autocomplete_limit';

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ScopeConfig $scopeConfig
    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;

        $this->limit = (int) $scopeConfig->getValue(
            self::CONFIG_AUTOCOMPLETE_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        $query = $this->queryFactory->get()->getQueryText();
        if (!$query) {
            return [];
        }

        $collection = $this->getSuggestCollection();
        $result = [];
        foreach ($collection as $item) {
            $resultItem = $this->itemFactory->create([
                'title' => $item->getQueryText(),
                'num_results' => $item->getNumResults(),
            ]);
            if ($resultItem->getTitle() == $query) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        return ($this->limit) ? array_splice($result, 0, $this->limit) : $result;
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    private function getSuggestCollection()
    {
        return $this->queryFactory->get()->getSuggestCollection();
    }
}
