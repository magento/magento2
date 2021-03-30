<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Jwe\Jwe;
use Magento\Framework\Jwt\Jwe\JweHeader;
use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\JwtUserToken\Model\Config\JwtAlgorithmSource;

/**
 * Generates JWT based on Magento configuration.
 */
class ConfigurableJwtGenerator implements JwtGeneratorInterface
{
    private const JWT_ALG_CONFIG_PATH = 'webapi/webapisecurity/jwt_alg';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var JwtManagerInterface
     */
    private $jwtManager;

    /**
     * @var JwtAlgorithmSource
     */
    private $algsSource;

    /**
     * @var EncryptionSettingsInterface[]
     */
    private $jwsEncryptions;

    /**
     * @var EncryptionSettingsInterface[]
     */
    private $jweEncryptions;

    /**
     * @param ScopeConfigInterface $config
     * @param JwtManagerInterface $jwtManager
     * @param JwtAlgorithmSource $algorithmSource
     * @param EncryptionSettingsInterface[] $jwsEncryptions
     * @param EncryptionSettingsInterface[] $jweEncryptions
     */
    public function __construct(
        ScopeConfigInterface $config,
        JwtManagerInterface $jwtManager,
        JwtAlgorithmSource $algorithmSource,
        array $jwsEncryptions,
        array $jweEncryptions
    ) {
        $this->config = $config;
        $this->jwtManager = $jwtManager;
        $this->algsSource = $algorithmSource;
        $this->jwsEncryptions = $jwsEncryptions;
        $this->jweEncryptions = $jweEncryptions;
    }

    /**
     * @inheritDoc
     */
    public function generate(
        array $protectedHeaders,
        array $publicHeaders,
        array $claims,
        UserContextInterface $userContext
    ): string {
        $alg = $this->config->getValue(self::JWT_ALG_CONFIG_PATH);
        $type = $this->algsSource->getAlgorithmType($alg);
        if ($type === JwtAlgorithmSource::ALG_TYPE_JWS) {
            if (!array_key_exists($alg, $this->jwsEncryptions)) {
                throw new \RuntimeException(sprintf('Do not have signature settings for algorithm "%s"', $alg));
            }

            return $this->jwtManager->create(
                new Jws(
                    [new JwsHeader($protectedHeaders)],
                    new ClaimsPayload($claims),
                    $publicHeaders ? [new JwsHeader($publicHeaders)] : null
                ),
                $this->jwsEncryptions[$alg]
            );
        } else {
            if (!array_key_exists($alg, $this->jweEncryptions)) {
                throw new \RuntimeException(sprintf('Do not have encryption settings for algorithm "%s"', $alg));
            }

            return $this->jwtManager->create(
                new Jwe(
                    new JweHeader($protectedHeaders),
                    new JweHeader($publicHeaders),
                    null,
                    new ClaimsPayload($claims)
                ),
                $this->jweEncryptions[$alg]
            );
        }
    }
}
