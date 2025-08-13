<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Resolver\Product;

use Magento\DownloadableGraphQl\Model\ConvertSamplesToArray;
use Magento\DownloadableGraphQl\Model\GetDownloadableProductSamples;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Resolver fetches downloadable product samples and formats it according to the GraphQL schema.
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

        /** @var Product $product */
        $product = $value['model'];

        $samples = $this->getDownloadableProductSamples->execute($product);
        $data = $this->convertSamplesToArray->execute($samples);
        return $data;
    }
}
