<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Type;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 */
class Options implements ResolverInterface
{
    /**
     * @var OptionCollection
     */
    private $optionCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param OptionCollection $optionCollection
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        OptionCollection $optionCollection,
        ValueFactory $valueFactory,
        MetadataPool $metadataPool
    ) {
        $this->optionCollection = $optionCollection;
        $this->valueFactory = $valueFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value[$linkField])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        $this->optionCollection->addProductId((int)$value[$linkField]);

        $result = function () use ($value, $linkField) {
            return $this->optionCollection->getAttributesByProductId((int)$value[$linkField]);
        };

        return $this->valueFactory->create($result);
    }
}
