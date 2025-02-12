<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Resolver\DownloadableCartItem;

use Magento\Catalog\Model\Product;
use Magento\DownloadableGraphQl\Model\ConvertLinksToArray;
use Magento\DownloadableGraphQl\Model\GetDownloadableProductLinks;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Resolver fetches downloadable cart item links and formats it according to the GraphQL schema.
 */
class Links implements ResolverInterface
{
    /**
     * @var GetDownloadableProductLinks
     */
    private $getDownloadableProductLinks;

    /**
     * @var ConvertLinksToArray
     */
    private $convertLinksToArray;

    /**
     * @param GetDownloadableProductLinks $getDownloadableProductLinks
     * @param ConvertLinksToArray $convertLinksToArray
     */
    public function __construct(
        GetDownloadableProductLinks $getDownloadableProductLinks,
        ConvertLinksToArray $convertLinksToArray
    ) {
        $this->getDownloadableProductLinks = $getDownloadableProductLinks;
        $this->convertLinksToArray = $convertLinksToArray;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var QuoteItem $quoteItem */
        $quoteItem = $value['model'];

        /** @var Product $product */
        $product = $quoteItem->getProduct();

        $selectedLinksIds = $product->getLinksPurchasedSeparately()
            ? explode(',', $quoteItem->getOptionByCode('downloadable_link_ids')->getValue() ?? '') : [];
        $links = $this->getDownloadableProductLinks->execute($product, $selectedLinksIds);

        $data = $this->convertLinksToArray->execute($links);
        return $data;
    }
}
