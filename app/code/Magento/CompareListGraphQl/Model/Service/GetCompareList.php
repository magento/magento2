<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Get compare list
 */
class CompareListService
{
    /**
     * @var ComparableItemsService
     */
    private $comparableItemsService;

    /**
     * @var ComparableAttributesService
     */
    private $comparableAttributesService;

    /**
     * @param ComparableItemsService $comparableItemsService
     * @param ComparableAttributesService $comparableAttributesService
     */
    public function __construct(
        ComparableItemsService $comparableItemsService,
        ComparableAttributesService $comparableAttributesService
    ) {
        $this->comparableItemsService = $comparableItemsService;
        $this->comparableAttributesService = $comparableAttributesService;
    }

    /**
     * Get compare list
     *
     * @param int $listId
     * @param ContextInterface $context
     * @param StoreInterface $store
     *
     * @return array
     */
    public function getCompareList(int $listId, ContextInterface $context, StoreInterface $store)
    {
        return [
            'list_id' => $listId,
            'items' => $this->comparableItemsService->getComparableItems($listId, $context, $store),
            'attributes' => $this->comparableAttributesService->getComparableAttributes($listId, $context)
        ];
    }
}
