<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProductGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\GroupedProductGraphQl\Model\Resolver\Products\Links\Collection;

/**
 * {@inheritdoc}
 */
class GroupedItems implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Collection
     */
    private $linksCollection;

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['id'])) {
            return null;
        }

        $this->linksCollection->addParentIdToFilter((int)$value['id']);

        $result = function () use ($value) {
            return $this->linksCollection->getGroupedLinksByParentId((int)$value['id']);
        };

        return $this->valueFactory->create($result);
    }
}
