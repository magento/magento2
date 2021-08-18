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

/**
 * Get compare list id by customer id
 */
class GetListIdByCustomerId
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
    }

    /**
     * Get listId by Customer ID
     *
     * @param int $customerId
     *
     * @return int|null
     */
    public function execute(int $customerId): ?int
    {
        if ($customerId) {
            /** @var CompareList $compareList */
            $compareList = $this->compareListFactory->create();
            $this->resourceCompareList->load($compareList, $customerId, 'customer_id');
            return (int)$compareList->getListId();
        }

        return null;
    }
}
