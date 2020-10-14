<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\CompareListIdToMaskedListId;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Get compare list
 */
class GetCompareList
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
     * @var CompareListIdToMaskedListId
     */
    private $compareListIdToMaskedListId;

    /**
     * @param ComparableItemsService $comparableItemsService
     * @param ComparableAttributesService $comparableAttributesService
     * @param CompareListIdToMaskedListId $compareListIdToMaskedListId
     */
    public function __construct(
        ComparableItemsService $comparableItemsService,
        ComparableAttributesService $comparableAttributesService,
        CompareListIdToMaskedListId $compareListIdToMaskedListId
    ) {
        $this->comparableItemsService = $comparableItemsService;
        $this->comparableAttributesService = $comparableAttributesService;
        $this->compareListIdToMaskedListId = $compareListIdToMaskedListId;
    }

    /**
     * Get compare list information
     *
     * @param int $listId
     * @param ContextInterface $context
     *
     * @return array
     */
    public function execute(int $listId, ContextInterface $context)
    {
        $store = $context->getExtensionAttributes()->getStore();
        $maskedListId = $this->compareListIdToMaskedListId->execute($listId);
        return [
            'uid' => $maskedListId,
            'items' => $this->comparableItemsService->getComparableItems($listId, $context, $store),
            'attributes' => $this->comparableAttributesService->getComparableAttributes($listId, $context)
        ];
    }
}
