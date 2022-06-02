<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Api\TokenReaderInterface;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkFactory;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Exception\JwtException;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

/**
 * Adobe Ims Token Reader
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class TokenReader implements TokenReaderInterface
{
    private const HEADER_ATTRIBUTE_X5U = 'x5u';

    /**
     * @var string
     */
    private string $cacheIdPrefix = 'AdminAdobeIms_';

    /**
     * @var string
     */
    private string $cacheId = '';

    /**
     * @var JwtManagerInterface
     */
    private JwtManagerInterface $jwtManager;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var JwkFactory
     */
    private JwkFactory $jwkFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var File
     */
    private File $driver;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var OauthHelper
     */
    private OauthHelper $oauthHelper;

    /**
     * @param JwtManagerInterface $jwtManager
     * @param CacheInterface $cache
     * @param ImsConfig $adminImsConfig
     * @param JwkFactory $jwkFactory
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param File $driver
     * @param Json $json
     * @param OauthHelper $oauthHelper
     */
    public function __construct(
        JwtManagerInterface $jwtManager,
        CacheInterface $cache,
        ImsConfig $adminImsConfig,
        JwkFactory $jwkFactory,
        LoggerInterface $logger,
        DateTime $dateTime,
        File $driver,
        Json $json,
        OauthHelper $oauthHelper
    ) {
        $this->jwtManager = $jwtManager;
        $this->cache = $cache;
        $this->adminImsConfig = $adminImsConfig;
        $this->jwkFactory = $jwkFactory;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->driver = $driver;
        $this->json = $json;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * Read data from a token.
     *
     * @param string $token
     * @return array
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws InvalidArgumentException
     */
    public function read(string $token): array
    {
        try {
            if (!$jwk = $this->getJWK($token)) {
                throw new AuthenticationException(__('Failed to get JWK'));
            }
            $jwt = $this->jwtManager->read($token, [Jwk::ALGORITHM_RS256 => new JwsSignatureJwks($jwk)]);
        } catch (JwtException $exception) {
            $this->logger->error($exception->getMessage());
            throw new AuthenticationException(__('Failed to read JWT token'));
        }

        if (!$jwt->getPayload() instanceof ClaimsPayloadInterface) {
            throw new AuthenticationException(__('JWT does not contain claims'));
        }
        /** @var ClaimsPayloadInterface $payload */
        $payload = $jwt->getPayload();
        $claims = $payload->getClaims();

        if (empty($claims['created_at']) || empty($claims['created_at']->getValue())) {
            throw new InvalidArgumentException(__('created_at not provided by the received JWT'));
        }
        if (empty($claims['expires_in']) || empty($claims['expires_in']->getValue())) {
            throw new InvalidArgumentException(__('expires_in not provided by the received JWT'));
        }

        $createdAt = (int)$claims['created_at']->getValue();
        $expiresIn = (int)$claims['expires_in']->getValue();
        if ($this->isTokenExpired($createdAt, $expiresIn)) {
            throw new AuthorizationException(__('Token has expired'));
        }

        return [
            'created_at' => $createdAt,
            'expires_in' => $expiresIn,
        ];
    }

    /**
     * JSON Web Key (JWK) to verify JSON Web Signature (JWS)
     *
     * @param string $token
     * @return false|Jwk
     */
    private function getJWK(string $token)
    {
        [$header] = explode(".", (string)$token);

        $decodedAdobeImsHeader = $this->json->unserialize(
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            base64_decode($header)
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
        );

        if (!isset($decodedAdobeImsHeader[self::HEADER_ATTRIBUTE_X5U])) {
            return false;
        }

        $certificateFileName = $decodedAdobeImsHeader[self::HEADER_ATTRIBUTE_X5U];
        $this->setCertificateCacheId($certificateFileName);

        if (!$certificateValue = $this->loadCertificateFromCache()) {
            $certificateUrl = $this->adminImsConfig->getCertificateUrl($certificateFileName);
            try {
                $certificateValue = $this->driver->fileGetContents($certificateUrl);
            } catch (FileSystemException $exception) {
                $this->logger->error($exception->getMessage());
                return false;
            }
            $this->saveCertificateInCache($certificateValue);
        }

        return $this->jwkFactory->createVerifyRs256($certificateValue);
    }

    /**
     * Load certificate from cache
     *
     * @return string|bool
     */
    private function loadCertificateFromCache()
    {
        return $this->cache->load($this->cacheId);
    }

    /**
     * Save certificate into cache
     *
     * @param string $certificateValue
     * @return void
     */
    private function saveCertificateInCache(string $certificateValue): void
    {
        $this->cache->save($certificateValue, $this->cacheId, []);
    }

    /**
     * Cache Id is based on prefix that is equal to module name and certificate file name that is in token header
     *
     * @param string $certificateFileName
     */
    private function setCertificateCacheId(string $certificateFileName): void
    {
        $this->cacheId = $this->cacheIdPrefix . $certificateFileName;
    }

    /**
     * Check if a token is expired
     *
     * @param int $createdAt
     * @param int $expiresIn
     * @return bool
     */
    private function isTokenExpired(int $createdAt, int $expiresIn): bool
    {
        $adobeIsTokenExpired = ($createdAt + $expiresIn) / 1000 <= $this->dateTime->gmtTimestamp();
        /* convert admin token lifetime hours to seconds */
        $adminTokenLifetime = $this->oauthHelper->getAdminTokenLifetime() * 3600;
        $magentoIsTokenExpired = ($createdAt + $adminTokenLifetime) <= $this->dateTime->gmtTimestamp();

        return $adobeIsTokenExpired || $magentoIsTokenExpired;
    }
}
