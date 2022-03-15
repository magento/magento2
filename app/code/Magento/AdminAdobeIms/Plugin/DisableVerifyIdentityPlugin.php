<?php

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\User\Model\User;

class DisableVerifyIdentityPlugin
{
    /** @var ImsConfig */
    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConfig $imsConfig
    ) {
        $this->imsConfig = $imsConfig;
    }

    /**
     * @param User $subject
     * @param callable $proceed
     * @param string $password
     * @return bool
     */
    public function aroundVerifyIdentity(User $subject, callable $proceed, $password): bool
    {
        if ($this->imsConfig->enabled() !== true) {
            return $proceed($password);
        }
        return true;
    }
}
