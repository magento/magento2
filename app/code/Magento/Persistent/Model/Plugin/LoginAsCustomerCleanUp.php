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
     * Cleans persistence cookie on sales representative login
     *
     * @param AuthenticateCustomerBySecretInterface $subject
     * @param string $secret
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(AuthenticateCustomerBySecretInterface $subject, string $secret)
    {
        if ($this->persistentSession->isPersistent()) {
            $this->persistentSession->getSession()->removePersistentCookie();
        }
        return $secret;
    }
}
