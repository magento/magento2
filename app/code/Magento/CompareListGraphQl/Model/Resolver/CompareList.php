<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get compare list
 */
class CompareList implements ResolverInterface
{
    /**
     * @var GetCompareList
     */
    private $getCompareList;

    /**
     * @var MaskedListIdToCompareListId
     */
    private $maskedListIdToCompareListId;

    /**
     * @param GetCompareList $getCompareList
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     */
    public function __construct(
        GetCompareList $getCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId
    ) {
        $this->getCompareList = $getCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
    }

    /**
     * Get compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (empty($args['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified'));
        }

        try {
            $listId = $this->maskedListIdToCompareListId->execute($args['uid'], $context->getUserId());
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        if (!$listId) {
            return null;
        }

        return $this->getCompareList->execute($listId, $context);
    }
}
