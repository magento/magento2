<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver\Type\Downloadable;

use Magento\Downloadable\Helper\Catalog\Product\Configuration;
use Magento\DownloadableGraphQl\Model\ConvertLinksToArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Item;

/**
 * Fetches the selected downloadable links
 */
class Links implements ResolverInterface
{
    /**
     * @var ConvertLinksToArray
     */
    private $convertLinksToArray;

    /**
     * @var Configuration
     */
    private $downloadableConfiguration;

    /**
     * @param ConvertLinksToArray $convertLinksToArray
     * @param Configuration $downloadableConfiguration
     */
    public function __construct(
        ConvertLinksToArray $convertLinksToArray,
        Configuration $downloadableConfiguration
    ) {
        $this->convertLinksToArray = $convertLinksToArray;
        $this->downloadableConfiguration = $downloadableConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['wishlistItemModel'])) {
            throw new LocalizedException(__('Missing key "wishlistItemModel" in Wishlist Item value data'));
        }
        /** @var Item $wishlistItem */
        $wishlistItem = $value['wishlistItemModel'];

        $links = $this->downloadableConfiguration->getLinks($wishlistItem);
        $links = $this->convertLinksToArray->execute($links);

        return $links;
    }
}
