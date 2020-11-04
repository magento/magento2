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
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

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
     * @param ValidateCustomer $validateCustomer
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     */
    public function __construct(
        ValidateCustomer $validateCustomer,
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList
    ) {
        $this->validateCustomer = $validateCustomer;
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
    }

    /**
     * Set customer to compare list
     *
     * @param int $listId
     * @param int $customerId
     *
     * @return bool
     *
     * @throws GraphQlAuthenticationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(int $listId, int $customerId): bool
    {
        if ($this->validateCustomer->execute($customerId)) {
            /** @var CompareList $compareListModel */
            $compareList = $this->compareListFactory->create();
            $this->resourceCompareList->load($compareList, $listId, 'list_id');
            $compareList->setCustomerId($customerId);
            $this->resourceCompareList->save($compareList);
            return true;
        }

        return false;
    }
}
