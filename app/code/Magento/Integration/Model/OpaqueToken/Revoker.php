<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenRevokerInterface;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection as TokenCollection;

class Revoker implements UserTokenRevokerInterface
{
    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * @param TokenCollectionFactory $tokenCollectionFactory
     */
    public function __construct(TokenCollectionFactory $tokenCollectionFactory)
    {
        $this->tokenCollectionFactory = $tokenCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function revokeFor(UserContextInterface $userContext): void
    {
        /** @var TokenCollection $tokenCollection */
        $tokenCollection = $this->tokenCollectionFactory->create();
        if ($userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $tokenCollection->addFilterByCustomerId($userContext->getUserId());
        } elseif ($userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            $tokenCollection->addFilterByAdminId($userContext->getUserId());
        } else {
            throw new \InvalidArgumentException('Opaque token revoker only works for customers and admin users');
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            throw new UserTokenException("The tokens couldn't be revoked.", $e);
        }
    }
}
