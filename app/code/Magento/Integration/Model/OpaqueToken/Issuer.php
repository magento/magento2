<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenIssuerInterface;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;

/**
 * Issues opaque tokens (legacy).
 */
class Issuer implements UserTokenIssuerInterface
{
    /**
     * @var TokenModelFactory
     */
    private $tokenFactory;

    /**
     * @var OauthHelper
     */
    private $helper;

    /**
     * @param TokenModelFactory $tokenFactory
     */
    public function __construct(TokenModelFactory $tokenFactory, OauthHelper $helper)
    {
        $this->tokenFactory = $tokenFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function create(UserContextInterface $userContext, UserTokenParametersInterface $params): string
    {
        /** @var Token $token */
        $token = $this->tokenFactory->create();

        if ($userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $token->setCustomerId($userContext->getUserId());
        } elseif ($userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            $token->setAdminId($userContext->getUserId());
        } else {
            throw new UserTokenException('Can only create tokens for customers and admin users');
        }
        $token->setUserType($userContext->getUserType());
        $token->setType(Token::TYPE_ACCESS);
        $token->setToken($this->helper->generateToken());
        $token->setSecret($this->helper->generateTokenSecret());
        if ($params->getForcedIssuedTime()) {
            if ($params->getForcedIssuedTime()->getTimezone()->getName() !== 'UTC') {
                throw new UserTokenException('Invalid forced issued time provided');
            }
            $token->setCreatedAt($params->getForcedIssuedTime()->format('Y-m-d H:i:s'));
        }
        $token = $token->save();


        return $token->getToken();
    }
}
