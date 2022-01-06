<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as CompareListResource;

/**
 * Create new Compare List
 */
class CreateCompareList
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var CompareListResource
     */
    private $compareListResource;

    /**
     * @param CompareListFactory $compareListFactory
     * @param CompareListResource $compareListResource
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        CompareListResource $compareListResource
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->compareListResource = $compareListResource;
    }

    /**
     * Created new compare list
     *
     * @param string $maskedId
     * @param int $customerId
     *
     * @return int
     */
    public function execute(string $maskedId, ?int $customerId = null): int
    {
        $compareList = $this->compareListFactory->create();
        $compareList->setListIdMask($maskedId);
        $compareList->setCustomerId($customerId);
        $this->compareListResource->save($compareList);

        return (int)$compareList->getListId();
    }
}
