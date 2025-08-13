<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\CompareListGraphQl\Model\Service\AddToCompareList;
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Add products item to compare list
 */
class AddProductsToCompareList implements ResolverInterface
{
    /**
     * @var AddToCompareList
     */
    private $addProductToCompareList;

    /**
     * @var GetCompareList
     */
    private $getCompareList;

    /**
     * @var MaskedListIdToCompareListId
     */
    private $maskedListIdToCompareListId;

    /**
     * @var GetListIdByCustomerId
     */
    private $getListIdByCustomerId;

    /**
     * @param AddToCompareList $addProductToCompareList
     * @param GetCompareList $getCompareList
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     * @param GetListIdByCustomerId $getListIdByCustomerId
     */
    public function __construct(
        AddToCompareList $addProductToCompareList,
        GetCompareList $getCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        GetListIdByCustomerId $getListIdByCustomerId
    ) {
        $this->addProductToCompareList = $addProductToCompareList;
        $this->getCompareList = $getCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
    }

    /**
     * Add products to compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (empty($args['input']['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified.'));
        }

        if (!isset($args['input']['products'])) {
            throw new GraphQlInputException(__('"products" value must be specified.'));
        }

        try {
            $listId = $this->maskedListIdToCompareListId->execute($args['input']['uid'], $context->getUserId());
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }


        if (!$listId) {
            throw new GraphQlInputException(__('"uid" value does not exist'));
        }

        try {
            $this->addProductToCompareList->execute($listId, $args['input']['products'], $context);
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        return $this->getCompareList->execute($listId, $context);
    }
}
