<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * The uid related data in the product graphql interface type
 */
class EntityIdToUid implements ResolverInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param MetadataPool $metadataPool
     * @param Uid $uidEncoder
     */
    public function __construct(
        MetadataPool $metadataPool,
        Uid $uidEncoder
    ) {
        $this->metadataPool = $metadataPool;
        $this->uidEncoder = $uidEncoder;
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

        $productId = $product->getData(
            $this->metadataPool->getMetadata(ProductInterface::class)->getIdentifierField()
        );

        return $this->uidEncoder->encode((string) $productId);
    }
}
