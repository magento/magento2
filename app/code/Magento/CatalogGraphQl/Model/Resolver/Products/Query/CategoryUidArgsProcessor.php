<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * Category UID processor class for category uid and category id arguments
 */
class CategoryUidArgsProcessor implements ArgumentsProcessorInterface
{
    private const ID = 'category_id';

    private const UID = 'category_uid';

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
        $idFilter = $args['filter'][self::ID] ?? [];
        $uidFilter = $args['filter'][self::UID] ?? [];
        if (!empty($idFilter)
            && !empty($uidFilter)
            && $fieldName === 'products') {
            throw new GraphQlInputException(
                __('`%1` and `%2` can\'t be used at the same time.', [self::ID, self::UID])
            );
        } elseif (!empty($uidFilter)) {
            if (isset($uidFilter['eq'])) {
                $args['filter'][self::ID]['eq'] = $this->uidEncoder->decode((string) $uidFilter['eq']);
            } elseif (!empty($uidFilter['in'])) {
                foreach ($uidFilter['in'] as $uid) {
                    $args['filter'][self::ID]['in'][] = $this->uidEncoder->decode((string) $uid);
                }
            }
            unset($args['filter'][self::UID]);
        }
        return $args;
    }
}
