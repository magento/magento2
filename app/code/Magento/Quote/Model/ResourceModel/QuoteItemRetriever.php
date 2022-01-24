<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ItemFactory;

/**
 * Class is used to Retrieve Quote Item by id
 */
class QuoteItemRetriever
{
    /**
     * @var ItemFactory
     */
    private $quoteItemFactory;

    /**
     * Constructor
     *
     * @param ItemFactory $quoteItemFactory
     */
    public function __construct(
        ItemFactory $quoteItemFactory
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
    }

    /**
     * Retrieve Quote Item Model
     *
     * @param int $quoteItemId
     * @return Item
     * @throws NoSuchEntityException
     */
    public function getById(int $quoteItemId): Item
    {
        $quoteItem = $this->quoteItemFactory->create()->load($quoteItemId);
        if (!$quoteItem->getId()) {
            // Quote Item does not exist
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Invalid Quote Item id %1', $quoteItemId)
            );
        }

        return $quoteItem;
    }
}
