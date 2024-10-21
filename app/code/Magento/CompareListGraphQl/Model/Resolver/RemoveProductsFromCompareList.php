<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\CompareListGraphQl\Model\Service\RemoveFromCompareList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Remove items from compare list
 */
class RemoveProductsFromCompareList implements ResolverInterface
{
    /**
     * @var GetCompareList
     */
    private $getCompareList;

    /**
     * @var RemoveFromCompareList
     */
    private $removeFromCompareList;

    /**
     * @var MaskedListIdToCompareListId
     */
    private $maskedListIdToCompareListId;

    /**
     * @var GetListIdByCustomerId
     */
    private $getListIdByCustomerId;

    /**
     * @param GetCompareList $getCompareList
     * @param RemoveFromCompareList $removeFromCompareList
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     * @param GetListIdByCustomerId $getListIdByCustomerId
     */
    public function __construct(
        GetCompareList $getCompareList,
        RemoveFromCompareList $removeFromCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        GetListIdByCustomerId $getListIdByCustomerId
    ) {
        $this->getCompareList = $getCompareList;
        $this->removeFromCompareList = $removeFromCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
    }

    /**
     * Remove products from compare list
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
     *
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input']['products'])) {
            throw new GraphQlInputException(__('"products" value must be specified.'));
        }

        if (empty($args['input']['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified.'));
        }

        try {
            $listId = $this->maskedListIdToCompareListId->execute($args['input']['uid'], $context->getUserId());
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        if (!$listId) {
            throw new GraphQlInputException(__('"uid" value does not exist'));
        }

        if ($userId = $context->getUserId()) {
            $customerListId = $this->getListIdByCustomerId->execute($userId);
            if ($listId === $customerListId) {
                $this->removeFromCompareList($customerListId, $args);
            }
        }

        try {
            $this->removeFromCompareList->execute($listId, $args['input']['products']);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(
                __('Something was wrong during removing products from compare list')
            );
        }

        return $this->getCompareList->execute($listId, $context);
    }

    /**
     * Remove products from compare list
     *
     * @param int $listId
     * @param array $args
     * @throws GraphQlInputException
     */
    private function removeFromCompareList(int $listId, array $args): void
    {
        try {
            $this->removeFromCompareList->execute($listId, $args['input']['products']);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(
                __('Something was wrong during removing products from compare list')
            );
        }
    }
}
