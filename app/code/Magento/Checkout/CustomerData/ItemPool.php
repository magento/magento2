<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Item pool
 * @since 2.0.0
 */
class ItemPool implements ItemPoolInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Default item id
     *
     * @var string
     * @since 2.0.0
     */
    protected $defaultItemId;

    /**
     * Item map. Key is item type, value is item object id in di
     *
     * @var array
     * @since 2.0.0
     */
    protected $itemMap;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $defaultItemId
     * @param array $itemMap
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $defaultItemId,
        array $itemMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->defaultItemId = $defaultItemId;
        $this->itemMap = $itemMap;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getItemData(Item $item)
    {
        return $this->get($item->getProductType())->getItemData($item);
    }

    /**
     * Get section source by name
     *
     * @param string $type
     * @return ItemInterface
     * @throws LocalizedException
     * @since 2.0.0
     */
    protected function get($type)
    {
        $itemId = isset($this->itemMap[$type]) ? $this->itemMap[$type] : $this->defaultItemId;
        $item = $this->objectManager->get($itemId);

        if (!$item instanceof ItemInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t extend \Magento\Checkout\CustomerData\ItemInterface', $type)
            );
        }
        return $item;
    }
}
