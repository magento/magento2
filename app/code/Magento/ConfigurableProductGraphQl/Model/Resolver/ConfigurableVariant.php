<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Type;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\ConfigurableProductGraphQl\Model\Variant\Collection;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;

/**
 * {@inheritdoc}
 */
class ConfigurableVariant implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $variantCollection;

    /**
     * @var OptionCollection
     */
    private $optionCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param Collection $variantCollection
     * @param OptionCollection $optionCollection
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        Collection $variantCollection,
        OptionCollection $optionCollection,
        ValueFactory $valueFactory
    ) {
        $this->variantCollection = $variantCollection;
        $this->optionCollection = $optionCollection;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value['id'])) {
            return null;
        }

        $this->variantCollection->addParentId((int)$value['id']);
        $this->optionCollection->addProductId((int)$value['id']);

        $result = function () use ($value) {
            $children = $this->variantCollection->getChildProductsByParentId((int)$value['id']) ?: [];
            $options = $this->optionCollection->getAttributesByProductId((int)$value['id']) ?: [];
            $variants = [];
            foreach ($children as $key => $child) {
                $variants[$key] = ['product' => $child];
                foreach ($options as $option) {
                    $code = $option['attribute_code'];
                    if (!isset($child[$code])) {
                        continue;
                    }

                    foreach ($option['values'] as $optionValue) {
                        if ($optionValue['value_index'] != $child[$code]) {
                            continue;
                        }
                        $variants[$key]['attributes'][] = [
                            'label' => $optionValue['label'],
                            'code' => $code,
                            'use_default_value' => $optionValue['use_default_value'],
                            'value_index' => $optionValue['value_index']
                        ];
                    }
                }
            }

            return $variants;
        };

        return $this->valueFactory->create($result);
    }
}
