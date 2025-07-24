<?php
/**
 * Copyright 2025 Adobe
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
        return (int)(
            $this->session->getLoggedAsCustomerAdminId()
                // This typo is kept for backward compatibility. Should be removed on Magento 2.5
                ?? $this->session->getLoggedAsCustomerAdmindId()
        );
    }
}
