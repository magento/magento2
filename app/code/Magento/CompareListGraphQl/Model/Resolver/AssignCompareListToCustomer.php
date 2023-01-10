<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\CompareListGraphQl\Model\Service\Customer\SetCustomerToCompareList;
use Magento\CompareListGraphQl\Model\Service\GetCompareList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Assign Customer to CompareList
 */
class AssignCompareListToCustomer implements ResolverInterface
{
    /**
     * @var SetCustomerToCompareList
     */
    private $setCustomerToCompareList;

    /**
     * @var MaskedListIdToCompareListId
     */
    private $maskedListIdToCompareListId;

    /**
     * @var GetCompareList
     */
    private $getCompareList;

    /**
     * @param SetCustomerToCompareList $setCustomerToCompareList
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     */
    public function __construct(
        SetCustomerToCompareList $setCustomerToCompareList,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        GetCompareList $getCompareList
    ) {
        $this->setCustomerToCompareList = $setCustomerToCompareList;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->getCompareList = $getCompareList;
    }

    /**
     * Assign compare list to customer
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
        if (empty($args['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified'));
        }

        if (!$context->getUserId()) {
            throw new GraphQlInputException(__('Customer must be logged'));
        }

        try {
            $listId = $this->maskedListIdToCompareListId->execute($args['uid']);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        if ($listId) {
            try {
                $result = $this->setCustomerToCompareList->execute($listId, $context->getUserId(), $context);
                if ($result) {
                    return [
                        'result' => true,
                        'compare_list' => $this->getCompareList->execute((int)$result->getListId(), $context)
                    ];
                }
            } catch (LocalizedException $exception) {
                throw new GraphQlInputException(
                    __('Something was wrong during assigning customer.')
                );
            }
        }

        return [
            'result' => false
        ];
    }
}
