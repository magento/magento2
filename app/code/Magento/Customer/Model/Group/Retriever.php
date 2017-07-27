<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Customer\Model\Session;

/**
 * Class for getting current customer group from customer session.
 */
class Retriever implements \Magento\Customer\Model\Group\RetrieverInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->getCustomerGroupId();
    }
}
