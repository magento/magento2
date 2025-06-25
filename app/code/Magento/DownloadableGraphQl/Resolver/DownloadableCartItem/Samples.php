<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Resolver\DownloadableCartItem;

use Magento\Catalog\Model\Product;
use Magento\DownloadableGraphQl\Model\ConvertSamplesToArray;
use Magento\DownloadableGraphQl\Model\GetDownloadableProductSamples;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Resolver fetches downloadable cart item samples and formats it according to the GraphQL schema.
 */
class Samples implements ResolverInterface
{
    /**
     * @var GetDownloadableProductSamples
     */
    private $getDownloadableProductSamples;

    /**
     * @var ConvertSamplesToArray
     */
    private $convertSamplesToArray;

    /**
     * @param GetDownloadableProductSamples $getDownloadableProductSamples
     * @param ConvertSamplesToArray $convertSamplesToArray
     */
    public function __construct(
        GetDownloadableProductSamples $getDownloadableProductSamples,
        ConvertSamplesToArray $convertSamplesToArray
    ) {
        $this->getDownloadableProductSamples = $getDownloadableProductSamples;
        $this->convertSamplesToArray = $convertSamplesToArray;
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

        if (!in_array($product->getTypeId(), ['downloadable', 'virtual'])) {
            throw new GraphQlInputException(
                __('Wrong product type. Links are available for Downloadable and Virtual product types')
            );
        }

        $samples = $this->getDownloadableProductSamples->execute($product);
        $data = $this->convertSamplesToArray->execute($samples);
        return $data;
    }
}
