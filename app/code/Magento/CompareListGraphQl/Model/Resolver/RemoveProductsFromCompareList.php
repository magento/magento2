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
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\CompareListGraphQl\Model\Service\RemoveFromCompareList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Remove items from compare list
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var Compare
     */
    private mixed $productCompareHelper;

    /**
     * @var CompareCookieManager
     */
    private CompareCookieManager $compareCookieManager;

    /**
     * @param GetCompareList $getCompareList
     * @param RemoveFromCompareList $removeFromCompareList
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     * @param GetListIdByCustomerId $getListIdByCustomerId
     * @param Compare|null $productCompareHelper
     * @param CompareCookieManager|null $compareCookieManager
     */
    public function __construct(
        GetCompareList $getCompareList,
        RemoveFromCompareList $removeFromCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        GetListIdByCustomerId $getListIdByCustomerId,
        ?Compare $productCompareHelper = null,
        ?CompareCookieManager $compareCookieManager = null
    ) {
        $this->getCompareList = $getCompareList;
        $this->removeFromCompareList = $removeFromCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
        $this->productCompareHelper = $productCompareHelper ?: ObjectManager::getInstance()
            ->get(Compare::class);
        $this->compareCookieManager = $compareCookieManager ?: ObjectManager::getInstance()
            ->get(CompareCookieManager::class);
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
        ?array $value = null,
        ?array $args = null
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
            $this->productCompareHelper->calculate();
            $this->compareCookieManager->invalidate();
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
