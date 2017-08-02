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
 * @since 2.0.0
 */
class CustomerSessionUserContext implements UserContextInterface
{
    /**
     * @var CustomerSession
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Initialize dependencies.
     *
     * @param CustomerSession $customerSession
     * @since 2.0.0
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserId()
    {
        return $this->_customerSession->getId();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_CUSTOMER;
    }
}
