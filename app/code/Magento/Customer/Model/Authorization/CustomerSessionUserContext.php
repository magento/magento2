<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Session-based customer user context
 */
class CustomerSessionUserContext implements UserContextInterface
{
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Initialize dependencies.
     *
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->_customerSession->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_CUSTOMER;
    }
}
