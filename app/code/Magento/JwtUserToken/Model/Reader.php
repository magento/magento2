<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\HeaderParameterInterface;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\JwtUserToken\Model\Data\Header;
use Magento\JwtUserToken\Model\Data\JwtTokenData;
use Magento\JwtUserToken\Model\Data\JwtUserContext;

class Reader implements UserTokenReaderInterface
{
    /**
     * @var JwtSettingsProviderInterface
     */
    private $settingsProvider;

    /**
     * @var JwtManagerInterface
     */
    private $jwtManager;

    /**
     * @param JwtManagerInterface $jwtManager
     * @param JwtSettingsProviderInterface $settingsProvider
     */
    public function __construct(JwtManagerInterface $jwtManager, JwtSettingsProviderInterface $settingsProvider)
    {
        $this->jwtManager = $jwtManager;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @inheritDoc
     */
    public function read(string $token): UserToken
    {
        try {
            $jwt = $this->jwtManager->read($token, $this->settingsProvider->prepareAllAccepted());
        } catch (JwtException $exception) {
            throw new UserTokenException('Failed to read JWT token', $exception);
        }

        if ($jwt instanceof JwsInterface) {
            $headerParams = array_merge(
                $this->extractHeaderParameters($jwt->getProtectedHeaders()),
                $this->extractHeaderParameters($jwt->getUnprotectedHeaders())
            );
        } elseif ($jwt instanceof JweInterface) {
            $headerParams = array_merge(
                $this->extractHeaderParameters($jwt->getPerRecipientUnprotectedHeaders()),
                $this->extractHeaderParameters($jwt->getSharedUnprotectedHeader()),
                $this->extractHeaderParameters($jwt->getProtectedHeader())
            );
        } else {
            $headerParams = $this->extractHeaderParameters($jwt->getHeader());
        }

        if (!$jwt->getPayload() instanceof ClaimsPayloadInterface) {
            throw new UserTokenException('JWT does not contain claims');
        }
        /** @var ClaimsPayloadInterface $payload */
        $payload = $jwt->getPayload();
        $claims = $payload->getClaims();
        if (empty($claims['uid']) || empty($claims['uid']->getValue())) {
            throw new UserTokenException('UserId (uid) time not provided by the received JWT');
        }
        if (empty($claims['utypid']) || empty($claims['utypid']->getValue())) {
            throw new UserTokenException('UserTypeId (utypid) time not provided by the received JWT');
        }
        if (empty($claims['iat']) || empty($claims['iat']->getValue())) {
            throw new UserTokenException('IssuedAt (iat) time not provided by the received JWT');
        }
        $iat = \DateTimeImmutable::createFromFormat('U', (string) $claims['iat']->getValue());
        if (empty($claims['exp']) || empty($claims['exp']->getValue())) {
            throw new UserTokenException('ExpiresAt (exp) time not provided by the received JWT');
        }
        $exp = \DateTimeImmutable::createFromFormat('U', (string) $claims['exp']->getValue());

        return new UserToken(
            new JwtUserContext((int) $claims['uid']->getValue(), (int) $claims['utypid']->getValue()),
            new JwtTokenData($iat, $exp, new Header($headerParams), $payload)
        );
    }

    /**
     * Extract header values from multiple headers.
     *
     * @param HeaderInterface|HeaderInterface[]|null $headers
     * @return HeaderParameterInterface[]
     */
    private function extractHeaderParameters($headers): array
    {
        $params = [];
        if ($headers) {
            if (!is_array($headers)) {
                $headers = [$headers];
            }

            foreach ($headers as $header) {
                foreach ($header->getParameters() as $parameter) {
                    $params[$parameter->getName()] = $parameter;
                }
            }
        }

        return $params;
    }
}
