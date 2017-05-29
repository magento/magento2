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

class DataProvider implements DataProviderInterface
{
    /**
     * Query factory
     *
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
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory
    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $collection = $this->getSuggestCollection();
        $query = $this->queryFactory->get()->getQueryText();
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
        return $result;
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
