<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Jose\Component\Core\JWT as CoreJwt;
use Jose\Component\Signature\JWSVerifier as NativeVerifier;
use Magento\Framework\Jwt\AlgorithmFactory;
use Magento\Framework\Jwt\ClaimChecker\Manager;
use Magento\Framework\Jwt\Data\Jwt;
use Magento\Framework\Jwt\KeyGeneratorInterface;
use Magento\Framework\Jwt\ManagementInterface;
use Magento\Framework\Jwt\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Implementation for JWS encode/decode/verification.
 */
class Management implements ManagementInterface
{
    /**
     * @var NativeVerifier
     */
    private $jwtVerifier;

    /**
     * @var AlgorithmFactory
     */
    private $algorithmFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var KeyGeneratorInterface
     */
    private $keyGenerator;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Manager
     */
    private $claimCheckerManager;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @param KeyGeneratorInterface $keyGenerator
     * @param SerializerInterface $serializer
     * @param AlgorithmFactory $algorithmFactory
     * @param Json $json
     * @param Manager $claimCheckerManager
     * @param BuilderFactory $builderFactory
     */
    public function __construct(
        KeyGeneratorInterface $keyGenerator,
        SerializerInterface $serializer,
        AlgorithmFactory $algorithmFactory,
        Json $json,
        Manager $claimCheckerManager,
        BuilderFactory $builderFactory
    ) {
        $this->keyGenerator = $keyGenerator;
        $this->serializer = $serializer;
        $this->algorithmFactory = $algorithmFactory;
        $this->json = $json;
        $this->claimCheckerManager = $claimCheckerManager;
        $this->builderFactory = $builderFactory;
    }

    /**
     * @inheritdoc
     */
    public function encode(array $claims): string
    {
        // as payload represented by url encode64 on json string,
        // the same claims structure with different key's order will get different payload hash
        ksort($claims);
        $payload = $this->json->serialize($claims);

        $jwsBuilder = $this->builderFactory->create($this->algorithmFactory->getAlgorithmManager());
        $jws = $jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature(
                $this->keyGenerator->generate()->getKey(),
                [
                    'alg' => $this->algorithmFactory->getAlgorithmName(),
                    'typ' => 'JWT'
                ]
            )
            ->build();

        return $this->serializer->serialize(new Jwt($jws));
    }

    /**
     * @inheritdoc
     */
    public function decode(string $token): array
    {
        $jws = $this->serializer->unserialize($token)
            ->getToken();

        if (!$this->verify($jws)) {
            throw new \InvalidArgumentException('JWT signature verification failed');
        }

        return $this->json->unserialize($jws->getPayload());
    }

    /**
     * Verifies JWS.
     *
     * @param CoreJwt $jws
     * @return bool
     * @throws \InvalidArgumentException in case if claims validation fails
     */
    private function verify(CoreJwt $jws): bool
    {
        $verifier = $this->getVerifier();
        if (!$verifier->verifyWithKey($jws, $this->keyGenerator->generate()->getKey(), 0)) {
            return false;
        };

        $payload = $this->json->unserialize($jws->getPayload());
        $this->claimCheckerManager->check($payload);

        return true;
    }

    /**
     * Gets native JWT verifier.
     *
     * @return NativeVerifier
     */
    private function getVerifier(): NativeVerifier
    {
        if ($this->jwtVerifier === null) {
            $this->jwtVerifier = new NativeVerifier($this->algorithmFactory->getAlgorithmManager());
        }
        return $this->jwtVerifier;
    }
}
