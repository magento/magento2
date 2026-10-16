<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Customer\Model\Session;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GetLoggedAsCustomerAdminId implements GetLoggedAsCustomerAdminIdInterface
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
    public function execute(): int
    {
        $adminId = $this->session->getLoggedAsCustomerAdminId();
        if ($adminId === null) {
            // Backward compatibility for session values written with the previous typo.
            $adminId = $this->session->getLoggedAsCustomerAdmindId();
        }

        return (int)$adminId;
    }
}
