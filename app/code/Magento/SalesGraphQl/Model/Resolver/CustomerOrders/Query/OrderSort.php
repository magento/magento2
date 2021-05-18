<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;
use Magento\GiftRegistryGraphQl\Mapper\GiftRegistryDataMapper;

/**
 *
 */
class OrderSort
{
    private const SORTABLE_FIELD_MAP = 'CustomerOrderSortableField';

    /**
     * @var DataMapperInterface
     */
    private $enumDataMapper;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param DataMapperInterface $enumDataMapper
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        DataMapperInterface $enumDataMapper,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->enumDataMapper = $enumDataMapper;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     *
     *
     * @param array $args
     * @return SortOrder[]
     */
    public function createSortOrders(array $args): array
    {
        $sortField = $this->getField($args['sort']['sort_field']);
        $sortOrder = $this->sortOrderBuilder
            ->setField($sortField)
            ->setDirection($args['sort']['sort_direction'])
            ->create();
        return [$sortOrder];
    }

    /**
     *
     *
     * @param string $field
     * @return string
     */
    private function getField(string $field): string
    {
        $enums = $this->enumDataMapper->getMappedEnums(self::SORTABLE_FIELD_MAP);

        return $enums[strtolower($field)];
    }
}
