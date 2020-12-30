<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\CompareListGraphQl\Model\Service\AddToCompareList;
use Magento\CompareListGraphQl\Model\Service\CreateCompareList as CreateCompareListService;
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
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
    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var GetListIdByCustomerId
     */
    private $getListIdByCustomerId;

    /**
     * @var AddToCompareList
     */
    private $addProductToCompareList;

    /**
     * @var GetCompareList
     */
    private $getCompareList;

    /**
     * @var CreateCompareListService
     */
    private $createCompareList;

    /**
     * @param Random $mathRandom
     * @param GetListIdByCustomerId $getListIdByCustomerId
     * @param AddToCompareList $addProductToCompareList
     * @param GetCompareList $getCompareList
     * @param CreateCompareListService $createCompareList
     */
    public function __construct(
        Random $mathRandom,
        GetListIdByCustomerId $getListIdByCustomerId,
        AddToCompareList $addProductToCompareList,
        GetCompareList $getCompareList,
        CreateCompareListService $createCompareList
    ) {
        $this->mathRandom = $mathRandom;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
        $this->addProductToCompareList = $addProductToCompareList;
        $this->getCompareList = $getCompareList;
        $this->createCompareList = $createCompareList;
    }

    /**
     * Create compare list
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
     * @throws GraphQlInputException
     * @throws LocalizedException
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
        $generatedListId = $this->mathRandom->getUniqueHash();
        $listId = 0;

        try {
            if ((0 === $customerId || null === $customerId)) {
                $listId = $this->createCompareList->execute($generatedListId);
                $this->addProductToCompareList->execute($listId, $products, $context);
            }

            if ($customerId) {
                $listId = $this->getListIdByCustomerId->execute($customerId);
                if ($listId) {
                    $this->addProductToCompareList->execute($listId, $products, $context);
                } else {
                    $listId = $this->createCompareList->execute($generatedListId, $customerId);
                    $this->addProductToCompareList->execute($listId, $products, $context);
                }
            }
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(
                __('Something was wrong during creating compare list')
            );
        }

        return $this->getCompareList->execute($listId, $context);
    }
}
