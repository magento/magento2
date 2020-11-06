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
        array $value = null,
        array $args = null
    ) {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getStoreId();
        if (empty($args['input']['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified.'));
        }

        if (!isset($args['input']['products'])) {
            throw new GraphQlInputException(__('"products" value must be specified.'));
        }

        $listId = $this->maskedListIdToCompareListId->execute($args['input']['uid']);

        if (!$listId) {
            throw new GraphQlInputException(__('"uid" value does not exist'));
        }

        if ($userId = $context->getUserId()) {
            $customerListId = $this->getListIdByCustomerId->execute($userId);
            if ($listId === $customerListId) {
                $this->addProductToCompareList->execute($customerListId, $args['input']['products'], $storeId);

                return $this->getCompareList->execute($customerListId, $context);
            }
        }

        $this->addProductToCompareList->execute($listId, $args['input']['products'], $storeId);

        return $this->getCompareList->execute($listId, $context);
    }
}
