<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get customer compare list
 */
class CustomerCompareList implements ResolverInterface
{
    /**
     * @var GetCompareList
     */
    private $getCompareList;

    /**
     * @var GetListIdByCustomerId
     */
    private $getListIdByCustomerId;

    /**
     * @param GetCompareList $getCompareList
     * @param GetListIdByCustomerId $getListIdByCustomerId
     */
    public function __construct(
        GetCompareList $getCompareList,
        GetListIdByCustomerId $getListIdByCustomerId
    ) {
        $this->getCompareList = $getCompareList;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
    }

    /**
     * Get customer compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $listId = $this->getListIdByCustomerId->execute((int)$context->getUserId());

        if (!$listId) {
            return null;
        }

        return $this->getCompareList->execute($listId, $context);
    }
}
