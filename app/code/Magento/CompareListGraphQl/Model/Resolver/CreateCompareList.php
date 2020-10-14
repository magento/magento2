<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\CompareListGraphQl\Model\Service\AddToCompareList;
use Magento\CompareListGraphQl\Model\Service\CreateCompareList as CreateCompareListService;
use Magento\CompareListGraphQl\Model\Service\CustomerService;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Math\Random;

/**
 * Class for creating compare list
 */
class CreateCompareList implements ResolverInterface
{
    private $compareItemFactory;

    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var Random
     */
    private $mathRandom;

    private $customerService;

    private $addProductToCompareList;

    private $getCompareList;

    private $maskedListIdToCompareListId;

    private $createCompareList;

    public function __construct(
        ItemFactory $compareItemFactory,
        CompareListFactory $compareListFactory,
        Random $mathRandom,
        CustomerService $customerService,
        AddToCompareList $addProductToCompareList,
        GetCompareList $getCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        CreateCompareListService $createCompareList
    ) {
        $this->compareItemFactory = $compareItemFactory;
        $this->compareListFactory = $compareListFactory;
        $this->mathRandom = $mathRandom;
        $this->customerService = $customerService;
        $this->addProductToCompareList = $addProductToCompareList;
        $this->getCompareList = $getCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->createCompareList = $createCompareList;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customerId = $context->getUserId();
        $products = !empty($args['input']['products']) ? $args['input']['products'] : [];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getStoreId();
        $generatedListId = $this->mathRandom->getUniqueHash();

        if ((0 === $customerId || null === $customerId)) {
            $listId = $this->createCompareList->execute($generatedListId);
            $this->addProductToCompareList->execute($listId, $products, $storeId);
        }

        if ($customerId) {
            $listId = $this->customerService->getListIdByCustomerId($customerId);
            if ($listId) {
                $this->addProductToCompareList->execute($listId, $products, $storeId);
            } else {
                $listId = $this->createCompareList->execute($generatedListId, $customerId);
                $this->addProductToCompareList->execute($listId, $products, $storeId);
            }
        }

        return $this->getCompareList->execute($listId, $context);
    }
}
