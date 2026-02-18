<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerCustomerIdInterface;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SetLoggedAsCustomerCustomerId implements SetLoggedAsCustomerCustomerIdInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId): void
    {
        $this->session->setLoggedAsCustomerCustomerId($customerId);
    }
}
