<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Plugin\Auth;

use Magento\Authorization\Model\UserContextInterface;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\Integration\Api\UserTokenIssuerInterface;

/**
 * Load the shared UserContext with data for the new user after a token is generated
 */
class TokenIssuer
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
     * Reset the shared user context from the user used to generate the token
     *
     * @param UserTokenIssuerInterface $issuer
     * @param string $result
     * @param UserContextInterface $userContext
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(
        UserTokenIssuerInterface $issuer,
        string $result,
        UserContextInterface $userContext
    ): string {
        $this->contextFactory->create($userContext);
        return $result;
    }
}
