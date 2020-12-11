<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Category UID processor class for category uid and category id arguments
 */
class CartItemUidArgsProcessor implements ArgumentsProcessorInterface
{
    private const ID = 'cart_item_id';

    private const UID = 'cart_item_uid';

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
     * Process the removeItemFromCart arguments for uids
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
        $filterKey = 'input';
        $idFilter = $args[$filterKey][self::ID] ?? [];
        $uidFilter = $args[$filterKey][self::UID] ?? [];
        if (!empty($idFilter)
            && !empty($uidFilter)
            && $fieldName === 'removeItemFromCart') {
            throw new GraphQlInputException(
                __('`%1` and `%2` can\'t be used at the same time.', [self::ID, self::UID])
            );
        } elseif (!empty($uidFilter)) {
            $args[$filterKey][self::ID] = $this->uidEncoder->decode((string)$uidFilter);
            unset($args[$filterKey][self::UID]);
        }
        return $args;
    }
}
