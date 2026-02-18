<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\CompareListGraphQl\Model\Service\CompareCookieManager;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\CompareListGraphQl\Model\Service\AddToCompareList;
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Add products item to compare list
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var Compare
     */
    private mixed $productCompareHelper;

    /**
     * @var CompareCookieManager
     */
    private CompareCookieManager $compareCookieManager;

    /**
     * @param AddToCompareList $addProductToCompareList
     * @param GetCompareList $getCompareList
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     * @param GetListIdByCustomerId $getListIdByCustomerId
     * @param Compare|null $productCompareHelper
     * @param CompareCookieManager|null $compareCookieManager
     */
    public function __construct(
        AddToCompareList $addProductToCompareList,
        GetCompareList $getCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        GetListIdByCustomerId $getListIdByCustomerId,
        ?Compare $productCompareHelper = null,
        ?CompareCookieManager $compareCookieManager = null
    ) {
        $this->addProductToCompareList = $addProductToCompareList;
        $this->getCompareList = $getCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
        $this->productCompareHelper = $productCompareHelper ?: ObjectManager::getInstance()
            ->get(Compare::class);
        $this->compareCookieManager = $compareCookieManager ?: ObjectManager::getInstance()
            ->get(CompareCookieManager::class);
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
            $this->productCompareHelper->calculate();
            $this->compareCookieManager->invalidate();
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        return $this->getCompareList->execute($listId, $context);
    }
}
