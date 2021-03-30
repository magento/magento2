<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Jwt\Claim\IssuedAt;
use Magento\Integration\Api\Data\UserTokenParameters;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenIssuerInterface;
use Magento\JwtUserToken\Model\Data\JwtTokenParameters;

class Issuer implements UserTokenIssuerInterface
{
    /**
     * @var JwtGeneratorInterface
     */
    private $jwtGenerator;

    /**
     * @param JwtGeneratorInterface $jwtGenerator
     */
    public function __construct(JwtGeneratorInterface $jwtGenerator)
    {
        $this->jwtGenerator = $jwtGenerator;
    }

    /**
     * @inheritDoc
     */
    public function create(UserContextInterface $userContext, UserTokenParameters $params): string
    {
        if (!$userContext->getUserId() || !$userContext->getUserType()) {
            throw new UserTokenException('User ID and Type ID cannot be empty');
        }

        $privateHeaders = [];
        $publicHeaders = [];
        $claims = [];
        $claims['uid'] = (int) $userContext->getUserId();
        $claims['utypid'] = (int) $userContext->getUserType();
        if ($params->getForcedIssuedTime()) {
            $claims['iat'] = new IssuedAt($params->getForcedIssuedTime(), true);
        }

        if ($jwtParams = $params->getExtensionAttributes()->getJwtParams()) {
            /** @var JwtTokenParameters $jwtParams */
            $privateHeaders = array_merge($privateHeaders, $jwtParams->getProtectedHeaderParameters());
            $publicHeaders = array_merge($publicHeaders, $jwtParams->getPublicHeaderParameters());
            $claims = array_merge($jwtParams->getClaims(), $claims);
        }

        return $this->jwtGenerator->generate($privateHeaders, $publicHeaders, $claims, $userContext);
    }
}
