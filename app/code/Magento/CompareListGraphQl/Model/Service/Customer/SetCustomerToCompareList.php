<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service\Customer;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as ResourceCompareList;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory as CompareItemsCollectionFactory;
use Magento\CompareListGraphQl\Model\Service\AddToCompareList;
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
     * @var Collection
     */
    private $items;

    /**
     * @var CompareItemsCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var AddToCompareList
     */
    private $addProductToCompareList;

    /**
     * @param ValidateCustomer $validateCustomer
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     */
    public function __construct(
        ValidateCustomer $validateCustomer,
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList,
        GetListIdByCustomerId $getListIdByCustomerId,
        CompareItemsCollectionFactory $itemCollectionFactory,
        AddToCompareList $addProductToCompareList
    ) {
        $this->validateCustomer = $validateCustomer;
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->addProductToCompareList = $addProductToCompareList;
    }

    /**
     * Set customer to compare list
     *
     * @param int $listId
     * @param int $customerId
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
            /** @var CompareList $compareListModel */
            $compareList = $this->compareListFactory->create();
            $customerListId = $this->getListIdByCustomerId->execute($customerId);
            $this->resourceCompareList->load($compareList, $listId, 'list_id');
            if ($customerListId) {
                $this->items = $this->itemCollectionFactory->create();
                $products = $this->items->getProductsByListId($listId);
                $this->addProductToCompareList->execute($customerListId, $products, $context);
                $this->resourceCompareList->delete($compareList);
                $compareList = $this->compareListFactory->create();
                $this->resourceCompareList->load($compareList, $customerListId, 'list_id');
                return $compareList;
            }
            $compareList->setCustomerId($customerId);
            $this->resourceCompareList->save($compareList);
            return $compareList;
        }

        return null;
    }
}
