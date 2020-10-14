<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\CompareList as CatalogCompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Model\ResourceModel\CompareList as CompareListResource;
use Magento\Customer\Model\Visitor;

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
     * @var Visitor
     */
    private $customerVisitor;

    /**
     * @param CompareListFactory $compareListFactory
     * @param CompareListResource $compareListResource
     * @param Visitor $customerVisitor
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        CompareListResource $compareListResource,
        Visitor $customerVisitor
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->compareListResource = $compareListResource;
        $this->customerVisitor = $customerVisitor;
    }

    public function createCompareList(string $listId, int $customerId)
    {
        /* @var $compareListModel CatalogCompareList */
        $compareListModel = $this->compareListFactory->create();
        $compareListModel->setListId($listId);
        $this->addVisitorToItem($compareListModel, $customerId);
        $compareListModel->save();
    }

    private function addVisitorToItem($model,int $customerId)
    {
        $model->setVisitorId($this->customerVisitor->getId());
        if ($customerId) {
            $model->setCustomerId($customerId);
        }

        return $this;
    }
}
