<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Observer;

use Magento\AdobeIms\Controller\Adminhtml\User\Logout;
use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\Authorization\Model\Role;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer to flush admin user token when user's role been changed.
 */
class FlushUsersTokensObserver implements ObserverInterface
{
    /**
     * @var FlushUserTokensInterface
     */
    private $flushUserTokens;

    /**
     * @param FlushUserTokensInterface $flushUserTokens
     */
    public function __construct(
        FlushUserTokensInterface $flushUserTokens
    ) {
        $this->flushUserTokens = $flushUserTokens;
    }

    /**
     * Flushes admin user tokens
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var RequestInterface $request */
        $request = $observer->getDataByKey('request');
        $resources = $request->getParam('resource', false);
        if (is_array($resources) && !$this->roleHasImsLogoutResource($resources)) {
            /** @var Role $role */
            $role = $observer->getDataByKey('object');
            $users = $role->getRoleUsers();
            foreach ($users as $userId) {
                $this->flushUserTokens->execute((int) $userId);
            }
        }
    }

    /**
     * Checks if the role has IMS Logout resource
     *
     * @param array $resources
     * @return bool
     */
    private function roleHasImsLogoutResource(array $resources): bool
    {
        return in_array(Logout::ADMIN_RESOURCE, $resources);
    }
}
