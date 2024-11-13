<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\CompareListIdToMaskedListId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Get products compare list
 */
class GetCompareList
{
    /**
     * @var GetComparableItems
     */
    private $comparableItemsService;

    /**
     * @var GetComparableAttributes
     */
    private $comparableAttributesService;

    /**
     * @var CompareListIdToMaskedListId
     */
    private $compareListIdToMaskedListId;

    /**
     * @param GetComparableItems $comparableItemsService
     * @param GetComparableAttributes $comparableAttributesService
     * @param CompareListIdToMaskedListId $compareListIdToMaskedListId
     */
    public function __construct(
        GetComparableItems $comparableItemsService,
        GetComparableAttributes $comparableAttributesService,
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
     * @throws GraphQlInputException
     */
    public function execute(int $listId, ContextInterface $context)
    {
        try {
            $maskedListId = $this->compareListIdToMaskedListId->execute($listId, $context->getUserId());
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
        $comparableItems = $this->comparableItemsService->execute($listId, $context);

        return [
            'uid' => $maskedListId,
            'items' => $comparableItems,
            'attributes' => $this->comparableAttributesService->execute($listId, $context),
            'item_count' => count($comparableItems)
        ];
    }
}
