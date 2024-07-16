<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Jwt\Claim\ExpirationTime;
use Magento\Framework\Jwt\Claim\IssuedAt;
use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\Framework\Jwt\Jwe\Jwe;
use Magento\Framework\Jwt\Jwe\JweHeader;
use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsSignatureSettingsInterface;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenIssuerInterface;
use Magento\JwtUserToken\Api\ConfigReaderInterface;
use Magento\JwtUserToken\Model\Data\JwtTokenParameters;

class Issuer implements UserTokenIssuerInterface
{
    /**
     * @var JwtManagerInterface
     */
    private $jwtManager;

    /**
     * @var JwtSettingsProviderInterface
     */
    private $settingsProvider;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @param JwtManagerInterface $jwtManager
     * @param JwtSettingsProviderInterface $settingsProvider
     * @param ConfigReaderInterface $configReader
     */
    public function __construct(
        JwtManagerInterface $jwtManager,
        JwtSettingsProviderInterface $settingsProvider,
        ConfigReaderInterface $configReader
    ) {
        $this->jwtManager = $jwtManager;
        $this->settingsProvider = $settingsProvider;
        $this->configReader = $configReader;
    }

    /**
     * @inheritDoc
     */
    public function create(UserContextInterface $userContext, UserTokenParametersInterface $params): string
    {
        if (!$userContext->getUserId() || !$userContext->getUserType()) {
            throw new UserTokenException('User ID and Type ID cannot be empty');
        }

        $protectedHeaders = [];
        $publicHeaders = [];
        $claims = [];
        $claims['uid'] = new PrivateClaim('uid', (int) $userContext->getUserId());
        $claims['utypid'] = new PrivateClaim('utypid', (int) $userContext->getUserType());
        if ($params->getForcedIssuedTime()) {
            $iat = $params->getForcedIssuedTime();
            if ($iat instanceof \DateTime) {
                $iat = \DateTimeImmutable::createFromMutable($iat);
            }
        } else {
            $iat = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }
        $claims['iat'] = new IssuedAt($iat, true);
        if ($userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            $ttl = $this->configReader->getAdminTtl();
        } elseif ($userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $ttl = $this->configReader->getCustomerTtl();
        } else {
            throw new \RuntimeException('Can only issue tokens for customers and admin users');
        }
        $claims['exp'] = new ExpirationTime($iat->add(new \DateInterval("PT{$ttl}M")), true);

        if ($jwtParams = $params->getExtensionAttributes()->getJwtParams()) {
            /** @var JwtTokenParameters $jwtParams */
            $protectedHeaders = array_merge($jwtParams->getProtectedHeaderParameters(), $protectedHeaders);
            $publicHeaders = array_merge($jwtParams->getPublicHeaderParameters(), $publicHeaders);
            $claims = array_merge($jwtParams->getClaims(), $claims);
        }

        $settings = $this->settingsProvider->prepareSettingsFor($userContext);
        if ($settings instanceof JwsSignatureSettingsInterface) {
            return $this->jwtManager->create(
                new Jws(
                    [new JwsHeader($protectedHeaders)],
                    new ClaimsPayload($claims),
                    $publicHeaders ? [new JwsHeader($publicHeaders)] : null
                ),
                $settings
            );
        } else {
            return $this->jwtManager->create(
                new Jwe(
                    new JweHeader($protectedHeaders),
                    new JweHeader($publicHeaders),
                    null,
                    new ClaimsPayload($claims)
                ),
                $settings
            );
        }
    }
}
