<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;

/**
 * Plugin for \Magento\Customer\Model\AccountManagement::changePassword method.
 */
class CustomerChangePassword
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
     * Logout from customer session after change password.
     *
     * @param AccountManagement $subject
     * @param bool $result
     * @return bool
     */
    public function afterChangePassword(
        AccountManagement $subject,
        $result
    ) {
        $this->session->logout();

        return $result;
    }
}
