<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Customer\Model\Session;
use Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerAdminIdInterface;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SetLoggedAsCustomerAdminId implements SetLoggedAsCustomerAdminIdInterface
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
    public function execute(int $adminId): void
    {
        $this->session->setLoggedAsCustomerAdmindId($adminId);
    }
}
