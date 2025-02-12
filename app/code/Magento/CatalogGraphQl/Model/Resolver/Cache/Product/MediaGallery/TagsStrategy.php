<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery\ChangeDetector;
use Magento\Framework\App\Cache\Tag\StrategyInterface;

class TagsStrategy implements StrategyInterface
{
    /**
     * @var ChangeDetector
     */
    private $mediaGalleryChangeDetector;

    /**
     * @param ChangeDetector $mediaGalleryChangeDetector
     */
    public function __construct(ChangeDetector $mediaGalleryChangeDetector)
    {
        $this->mediaGalleryChangeDetector = $mediaGalleryChangeDetector;
    }

    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        if ($object instanceof Product &&
            !$object->isObjectNew() &&
            $this->mediaGalleryChangeDetector->isChanged($object)
        ) {
            return [
                sprintf('%s_%s', ResolverCacheIdentity::CACHE_TAG, $object->getId())
            ];
        }

        return [];
    }
}
