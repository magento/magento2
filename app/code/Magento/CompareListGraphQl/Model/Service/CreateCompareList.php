<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ListIdMaskFactory;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as CompareListResource;
use Magento\Catalog\Model\ResourceModel\Product\Compare\ListIdMask as ListIdMaskResource;

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
     * @var ListIdMaskFactory
     */
    private $maskedListIdFactory;

    /**
     * @var ListIdMaskResource
     */
    private $maskedListIdResource;

    /**
     * @param CompareListFactory $compareListFactory
     * @param CompareListResource $compareListResource
     * @param ListIdMaskFactory $maskedListIdFactory
     * @param ListIdMaskResource $maskedListIdResource
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        CompareListResource $compareListResource,
        ListIdMaskFactory $maskedListIdFactory,
        ListIdMaskResource $maskedListIdResource
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->compareListResource = $compareListResource;
        $this->maskedListIdFactory = $maskedListIdFactory;
        $this->maskedListIdResource = $maskedListIdResource;
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
        $compareList->setCustomerId($customerId);
        $this->compareListResource->save($compareList);

        $maskedListId = $this->maskedListIdFactory->create();
        $maskedListId->setListId($compareList->getListId());
        $maskedListId->setMaskedId($maskedId);
        $this->maskedListIdResource->save($maskedListId);

        return (int)$compareList->getListId();
    }
}
