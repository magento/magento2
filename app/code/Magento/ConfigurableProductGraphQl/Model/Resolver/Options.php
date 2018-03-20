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
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;

/**
 * {@inheritdoc}
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
     * @param OptionCollection $optionCollection
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        OptionCollection $optionCollection,
        ValueFactory $valueFactory
    ) {
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

        $this->optionCollection->addProductId((int)$value['id']);

        $result = function () use ($value) {
            return $this->optionCollection->getAttributesByProductId((int)$value['id']) ?: [];
        };

        return $this->valueFactory->create($result);
    }
}
