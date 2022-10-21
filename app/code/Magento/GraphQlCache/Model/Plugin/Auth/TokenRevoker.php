<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Plugin\Auth;

use Magento\Authorization\Model\UserContextInterface;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\Integration\Api\UserTokenRevokerInterface;
use Magento\Integration\Model\CustomUserContext;

/**
 * Load the shared UserContext with data for guest after a token is revoked
 */
class TokenRevoker
{
    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @param ContextFactoryInterface $contextFactory
     */
    public function __construct(ContextFactoryInterface $contextFactory)
    {
        $this->contextFactory = $contextFactory;
    }

    /**
     * Reset the shared user context to guest after a token is revoked
     *
     * @param UserTokenRevokerInterface $revoker
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRevokeFor(UserTokenRevokerInterface $revoker): void
    {
        $this->contextFactory->create(new CustomUserContext(0, UserContextInterface::USER_TYPE_GUEST));
    }
}
