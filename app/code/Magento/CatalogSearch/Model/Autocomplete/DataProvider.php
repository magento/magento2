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

/**
 * Class \Magento\CatalogSearch\Model\Autocomplete\DataProvider
 *
 * @since 2.0.0
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Query factory
     *
     * @var QueryFactory
     * @since 2.0.0
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     * @since 2.0.0
     */
    protected $itemFactory;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getSuggestCollection()
    {
        return $this->queryFactory->get()->getSuggestCollection();
    }
}
