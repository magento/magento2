<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\CompareList as CompareListResource;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;

class AddToList
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var CompareList
     */
    private $compareList;

    /**
     * @var CompareListResource
     */
    private $compareListResource;

    /**
     * Customer session
     *
     * @var Session
     */
    private $customerSession;

    /**
     * Customer visitor
     *
     * @var Visitor
     */
    private $customerVisitor;

    /**
     * @param CompareListFactory $compareListFactory
     * @param CompareList $compareList
     * @param CompareListResource $compareListResource
     * @param Session $customerSession
     * @param Visitor $customerVisitor
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        CompareList $compareList,
        CompareListResource $compareListResource,
        Session $customerSession,
        Visitor $customerVisitor
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->compareList = $compareList;
        $this->compareListResource = $compareListResource;
        $this->customerSession = $customerSession;
        $this->customerVisitor = $customerVisitor;
    }

    /**
     * Get list_id
     *
     * @return int
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->getListIdByCustomerId();
        }

        return $this->getListIdByVisitorId();
    }

    /**
     * Set customer from visitor
     */
    public function setCustomerFromVisitor()
    {
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return $this;
        }

        $visitorId = $this->customerVisitor->getId();
        $compareListModel = $this->compareListFactory->create();
        $this->compareListResource->load($compareListModel, $visitorId, 'visitor_id');
        $compareListModel->setCustomerId($customerId);
        $compareListModel->save();
    }

    /**
     * Get list_id for visitor
     *
     * @return int
     */
    private function getListIdByVisitorId()
    {
        $visitorId = $this->customerVisitor->getId();
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
     * @return int
     */
    private function getListIdByCustomerId()
    {
        $customerId = $this->customerSession->getCustomerId();
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
        /* @var $compareList CompareList */
        $compareList = $this->compareListFactory->create();
        $compareList->setVisitorId($visitorId);
        $compareList->setCustomerId($customerId);
        $compareList->save();

        return (int)$compareList->getId();
    }
}
