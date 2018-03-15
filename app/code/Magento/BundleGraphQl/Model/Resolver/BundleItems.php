<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\BundleGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Bundle\Model\Product\Type;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\BundleGraphQl\Model\Resolver\Options\Collection;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * {@inheritdoc}
 */
class BundleItems implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $bundleOptionCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param Collection $bundleOptionCollection
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        Collection $bundleOptionCollection,
        ValueFactory $valueFactory
    ) {
        $this->bundleOptionCollection = $bundleOptionCollection;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Fetch and format bundle option items.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value['id']) || !isset($value['sku'])) {
            return null;
        }

        $this->bundleOptionCollection->addParentFilterData((int)$value['id'], $value['sku']);

        $result = function () use ($value) {
            return $this->bundleOptionCollection->getOptionsByParentId((int)$value['id']);
        };

        return $this->valueFactory->create($result);
    }
}
