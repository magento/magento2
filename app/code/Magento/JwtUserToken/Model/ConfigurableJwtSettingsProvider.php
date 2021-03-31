<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Jwt\EncryptionSettingsInterface;
use Magento\Framework\Jwt\Jwe\JweEncryptionJwks;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\JwtUserToken\Api\ConfigReaderInterface;

/**
 * Provides JWT settings based on Magento config.
 */
class ConfigurableJwtSettingsProvider implements JwtSettingsProviderInterface
{
    /**
     * @var EncryptionSettingsInterface[]
     */
    private $jwsEncryptions;

    /**
     * @var EncryptionSettingsInterface[]
     */
    private $jweEncryptions;

    /**
     * @var SecretBasedJwksFactory
     */
    private $secretBasedJwkFactory;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @param SecretBasedJwksFactory $secretBasedJwkFactory
     * @param ConfigReaderInterface $configReader
     * @param EncryptionSettingsInterface[] $jwsEncryptions Additional JWS settings.
     * @param EncryptionSettingsInterface[] $jweEncryptions Additional JWE settings.
     */
    public function __construct(
        SecretBasedJwksFactory $secretBasedJwkFactory,
        ConfigReaderInterface $configReader,
        array $jwsEncryptions = [],
        array $jweEncryptions = []
    ) {
        $this->jwsEncryptions = $jwsEncryptions;
        $this->jweEncryptions = $jweEncryptions;
        $this->secretBasedJwkFactory = $secretBasedJwkFactory;
        $this->configReader = $configReader;
    }

    public function prepareSettingsFor(UserContextInterface $userContext): EncryptionSettingsInterface
    {
        return $this->prepareAllAccepted()[0];
    }

    /**
     * @inheritDoc
     */
    public function prepareAllAccepted(): array
    {
        $algorithm = $this->configReader->getJwtAlgorithm();
        $type = $this->configReader->getJwtAlgorithmType($algorithm);
        if ($type === ConfigReaderInterface::JWT_TYPE_JWS) {
            if (!array_key_exists($algorithm, $this->jwsEncryptions)) {
                //Try to create default settings.
                try {
                    $this->jwsEncryptions[$algorithm] = new JwsSignatureJwks(
                        $this->secretBasedJwkFactory->createFor($algorithm)
                    );
                } catch (\InvalidArgumentException $exception) {
                    //Failed to create
                    $x=1;
                }
            }
            if (!array_key_exists($algorithm, $this->jwsEncryptions)) {
                throw new \RuntimeException('JWT settings for algorithm "' .$algorithm .'" not found');
            }

            return [$this->jwsEncryptions[$algorithm]];
        } else {
            if (!array_key_exists($algorithm, $this->jweEncryptions)) {
                //Try to create default settings.
                try {
                    $contentAlg = $this->configReader->getJweContentAlgorithm();
                    $this->jweEncryptions[$algorithm] = new JweEncryptionJwks(
                        $this->secretBasedJwkFactory->createFor($algorithm),
                        $contentAlg
                    );
                } catch (\InvalidArgumentException $exception) {
                    //Failed to create
                }
            }
            if (!array_key_exists($algorithm, $this->jweEncryptions)) {
                throw new \RuntimeException('JWT settings for algorithm "' . $algorithm . '" not found');
            }

            return [$this->jweEncryptions[$algorithm]];
        }
    }
}
