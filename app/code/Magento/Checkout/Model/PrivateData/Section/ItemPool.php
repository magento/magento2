<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\PrivateData\Section;

use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Item pool
 */
class ItemPool implements ItemPoolInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Default item id
     *
     * @var string
     */
    protected $defaultItemId;

    /**
     * Item map. Key is item type, value is item object id in di
     *
     * @var array
     */
    protected $itemMap;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $defaultItemId
     * @param array $sectionSourceMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $defaultItemId,
        array $sectionSourceMap
    ) {
        $this->objectManager = $objectManager;
        $this->defaultItemId = $defaultItemId;
        $this->itemMap = $sectionSourceMap;
    }

    /**
     * {@inheritdoc}
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
     */
    protected function get($type)
    {
        $itemId = isset($this->itemMap[$type]) ? $this->itemMap[$type] : $this->defaultItemId;
        $item = $this->objectManager->get($itemId);

        if (!$item instanceof ItemInterface) {
            throw new LocalizedException(
                __('%s doesn\'t extends \Magento\Checkout\Model\PrivateData\Section\ItemInterface', $type)
            );
        }
        return $item;
    }
}
