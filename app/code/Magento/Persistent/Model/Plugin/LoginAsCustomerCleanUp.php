<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Plugin;

use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\Persistent\Helper\Session as PersistentSession;

class LoginAsCustomerCleanUp
{
    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @param PersistentSession $persistentSession
     */
    public function __construct(PersistentSession $persistentSession)
    {
        $this->persistentSession = $persistentSession;
    }

    /**
     * Disable persistence for sales representative login
     *
     * @param AuthenticateCustomerBySecretInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(AuthenticateCustomerBySecretInterface $subject)
    {
        if ($this->persistentSession->isPersistent()) {
            $this->persistentSession->getSession()->removePersistentCookie();
        }
    }
}
