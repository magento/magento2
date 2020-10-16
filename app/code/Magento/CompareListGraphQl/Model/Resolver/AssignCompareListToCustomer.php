<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\CompareListGraphQl\Model\Service\CustomerService;
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
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var MaskedListIdToCompareListId
     */
    private $maskedListIdToCompareListId;

    /**
     * @param CustomerService $customerService
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     */
    public function __construct(
        CustomerService $customerService,
        MaskedListIdToCompareListId $maskedListIdToCompareListId
    ) {
        $this->customerService = $customerService;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
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
        if (!isset($args['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified'));
        }

        if (!$context->getUserId()) {
            throw new GraphQlInputException(__('Customer must be logged'));
        }

        $listId = $this->maskedListIdToCompareListId->execute($args['uid']);
        $result = false;

        if ($listId) {
            try {
                $result = $this->customerService->setCustomerToCompareList($listId, $context->getUserId());
            } catch (LocalizedException $exception) {
                throw new GraphQlInputException(
                    __('Something was wrong during assigning customer.')
                );
            }
        }

        return $result;
    }
}
