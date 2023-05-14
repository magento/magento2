<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Customer\Model\AccountManagementApi;

/**
 * Plugin to validate anonymous request for synchronous operations containing group id.
 */
class SyncRequestCustomerGroupAuthorization
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     *
     * @param AuthorizationInterface|null $authorization
     */
    public function __construct(
        AuthorizationInterface $authorization = null
    ) {
        $objectManager = ObjectManager::getInstance();
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
    }

    /**
     * Validate groupId for anonymous request
     *
     * @param AccountManagementApi $accountManagementApi
     * @param CustomerInterface $customer
     * @param string|null $password
     * @param string $redirectUrl
     * @return void
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreateAccount(
        AccountManagementApi $accountManagementApi,
        CustomerInterface    $customer,
        string               $password = null,
        string               $redirectUrl = ''
    ): void {
        $groupId = $customer->getGroupId();
        if (isset($groupId) && !$this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
            $params = ['resources' => self::ADMIN_RESOURCE];
            throw new AuthorizationException(
                __("The consumer isn't authorized to access %resources.", $params)
            );
        }
    }
}
