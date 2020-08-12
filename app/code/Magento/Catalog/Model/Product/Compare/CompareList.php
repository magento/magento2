<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\CompareList as CatalogCompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\CompareList as CompareListResource;

class CompareList
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
     * Get list_id
     *
     * @param Item $item
     *
     * @return int
     */
    public function getListId(Item $item)
    {
        if ($customerId = $item->getCustomerId()) {
            return $this->getListIdByCustomerId($customerId);
        }

        return $this->getListIdByVisitorId($item->getVisitorId());
    }

    /**
     * Get list_id for visitor
     *
     * @param $visitorId
     *
     * @return int
     */
    private function getListIdByVisitorId($visitorId)
    {
        $compareListModel = $this->compareListFactory->create();
        $this->compareListResource->load($compareListModel, $visitorId, 'visitor_id');
        if ($compareListId = $compareListModel->getId()) {
            return (int)$compareListId;
        }

        return $this->createCompareList($visitorId, null);
    }

    /**
     * Get list_id for logged customers
     *
     * @param $customerId
     *
     * @return int
     */
    private function getListIdByCustomerId($customerId)
    {
        $compareListModel = $this->compareListFactory->create();
        $this->compareListResource->load($compareListModel, $customerId, 'customer_id');

        if ($compareListId = $compareListModel->getId()) {
            return (int)$compareListId;
        }

        return $this->createCompareList(0, $customerId);
    }

    /**
     * Create new compare list
     *
     * @param $visitorId
     * @param $customerId
     *
     * @return int
     */
    private function createCompareList($visitorId, $customerId)
    {
        /* @var $compareList CatalogCompareList */
        $compareList = $this->compareListFactory->create();
        $compareList->setVisitorId($visitorId);
        $compareList->setCustomerId($customerId);
        $compareList->save();

        return (int)$compareList->getId();
    }
}
