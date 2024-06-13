<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service\Customer;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as ResourceCompareList;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item as ResourceCompareItem;
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\CompareListGraphQl\Model\Service\Customer\MergeCompareLists;
use Magento\CompareListGraphQl\Model\Service\Customer\ValidateCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Assign customer to compare list
 */
class SetCustomerToCompareList
{
    /**
     * @var ValidateCustomer
     */
    private $validateCustomer;

    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var GetListIdByCustomerId
     */
    private $getListIdByCustomerId;

    /**
     * @var ResourceCompareItem
     */
    private $resourceCompareItem;

    /**
     * @var MergeCompareLists
     */
    private $mergeCompareLists;

    /**
     * @param ValidateCustomer $validateCustomer
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     * @param GetListIdByCustomerId $getListIdByCustomerId
     * @param ResourceCompareItem $resourceCompareItem
     * @param MergeCompareLists $mergeCompareLists
     */
    public function __construct(
        ValidateCustomer $validateCustomer,
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList,
        GetListIdByCustomerId $getListIdByCustomerId,
        ResourceCompareItem $resourceCompareItem,
        MergeCompareLists $mergeCompareLists
    ) {
        $this->validateCustomer = $validateCustomer;
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
        $this->resourceCompareItem = $resourceCompareItem;
        $this->mergeCompareLists = $mergeCompareLists;
    }

    /**
     * Set customer to compare list
     *
     * @param int $listId
     * @param int $customerId
     * @param ContextInterface $context
     *
     * @return CompareList
     *
     * @throws GraphQlAuthenticationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(int $listId, int $customerId, ContextInterface $context): ?CompareList
    {
        if ($this->validateCustomer->execute($customerId)) {
            $compareList = $this->compareListFactory->create();
            $customerListId = $this->getListIdByCustomerId->execute($customerId);
            $this->resourceCompareList->load($compareList, $listId, 'list_id');
            if (!$compareList->getListId()) {
                throw new GraphQlNoSuchEntityException(
                    __('The compare list with ID "%list_id" does not exist.', ['list_id' => $listId])
                );
            }
            if ($customerListId) {
                return $this->mergeCompareLists->execute($listId, $customerListId, $context);
            }

            $this->resourceCompareList->beginTransaction();
            try {
                $compareList->setCustomerId($customerId);
                $this->resourceCompareList->save($compareList);
                $this->resourceCompareItem->updateCustomerIdForListItems($listId, $customerId);
                $this->resourceCompareList->commit();
            } catch (\Exception $e) {
                $this->resourceCompareList->rollBack();
                throw $e;
            }

            return $compareList;
        }

        return null;
    }
}
