<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\GroupManagementInterface;

/**
 * Class for getting customer group from quote session for adminhtml area.
 */
class CustomerGroupRetriever implements \Magento\Customer\Model\Group\RetrieverInterface
{
    /**
     * @var Quote
     */
    private $quoteSession;

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @param Quote $quoteSession
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(Quote $quoteSession, GroupManagementInterface $groupManagement)
    {
        $this->quoteSession = $quoteSession;
        $this->groupManagement = $groupManagement;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId()
    {
        if ($this->quoteSession->getQuoteId() && $this->quoteSession->getQuote()) {
            return $this->quoteSession->getQuote()->getCustomerGroupId();
        }
        return $this->groupManagement->getNotLoggedInGroup()->getId();
    }
}
