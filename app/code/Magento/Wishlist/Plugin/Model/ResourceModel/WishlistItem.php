<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\PageCache\Model\Cache\Type  as PageCache;
use Magento\Wishlist\Model\ResourceModel\Item;

/**
 * Cleans up wishlist items referencing the product qty being updated
 */
class WishlistItem
{
    /**
     * @var PageCache
     */
    private $pageCache;

    /**
     * @param PageCache $pageCache
     */
    public function __construct(PageCache $pageCache)
    {
        $this->pageCache = $pageCache;
    }

    /**
     * Cleans up cache for wishlist item product qty update
     *
     * @param Item $subject
     * @param Item $result
     * @param AbstractModel $object
     *
     * @return Item
     * @throws LocalizedException
     */
    public function afterSave(Item $subject, Item $result, AbstractModel $object): Item
    {
        if ($subject->hasDataChanged($object)) {
            $product = $object->getProduct();

            if (!empty($product)) {
                $this->pageCache->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                    [Product::CACHE_TAG . '_' . $product->getId()]
                );
            }
        }

        return $result;
    }
}
