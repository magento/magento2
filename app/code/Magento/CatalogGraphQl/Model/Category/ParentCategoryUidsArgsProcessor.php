<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * Parent Category UID processor class for category uid and category id arguments
 */
class ParentCategoryUidsArgsProcessor implements ArgumentsProcessorInterface
{
    private const ID = 'parent_id';

    private const UID = 'parent_category_uid';

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param Uid $uidEncoder
     */
    public function __construct(Uid $uidEncoder)
    {
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Composite processor that loops through available processors for arguments that come from graphql input
     *
     * @param string $fieldName,
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function process(
        string $fieldName,
        array $args
    ): array {
        $filterKey = 'filters';
        $parentUidFilter = $args[$filterKey][self::UID] ?? [];
        $parentIdFilter = $args[$filterKey][self::ID] ?? [];
        if (!empty($parentIdFilter)
            && !empty($parentUidFilter)
            && ($fieldName === 'categories' || $fieldName === 'categoryList')) {
            throw new GraphQlInputException(
                __('`%1` and `%2` can\'t be used at the same time.', [self::ID, self::UID])
            );
        } elseif (!empty($parentUidFilter)) {
            if (isset($parentUidFilter['eq'])) {
                $args[$filterKey][self::ID]['eq'] = $this->uidEncoder->decode(
                    $parentUidFilter['eq']
                );
            } elseif (!empty($parentUidFilter['in'])) {
                foreach ($parentUidFilter['in'] as $parentUids) {
                    $args[$filterKey][self::ID]['in'][] = $this->uidEncoder->decode($parentUids);
                }
            }
            unset($args[$filterKey][self::UID]);
        }
        return $args;
    }
}
