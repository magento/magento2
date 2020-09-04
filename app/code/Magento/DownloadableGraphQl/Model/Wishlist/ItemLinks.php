<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Wishlist;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Downloadable\Helper\Catalog\Product\Configuration;
use Magento\DownloadableGraphQl\Model\ConvertLinksToArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Fetches the selected item downloadable links
 */
class ItemLinks implements ResolverInterface
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
        if (!$value['itemModel'] instanceof ItemInterface) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => ItemInterface::class
            ]));
        }
        /** @var ItemInterface $wishlistItem */
        $itemItem = $value['itemModel'];

        $links = $this->downloadableConfiguration->getLinks($itemItem);
        $links = $this->convertLinksToArray->execute($links);

        return $links;
    }
}
