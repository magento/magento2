<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\CompareList;

use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;

/**
 * @inheritdoc
 */
class CustomerIdByHashedIdProvider implements CustomerIdByHashedIdProviderInterface
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
     * @inheritdoc
     */
    public function get(string $hashedListId): int
    {
        $compareList = $this->compareListFactory->create();
        $this->resourceCompareList->load($compareList, $hashedListId, 'hashed_id');

        return (int)$compareList->getCustomerId();
    }
}
