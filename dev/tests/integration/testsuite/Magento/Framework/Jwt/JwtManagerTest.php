<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Magento\Framework\Jwt\Claim\ExpirationTime;
use Magento\Framework\Jwt\Claim\IssuedAt;
use Magento\Framework\Jwt\Claim\Issuer;
use Magento\Framework\Jwt\Claim\JwtId;
use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\Framework\Jwt\Claim\Subject;
use Magento\Framework\Jwt\Header\Critical;
use Magento\Framework\Jwt\Header\PrivateHeaderParameter;
use Magento\Framework\Jwt\Header\PublicHeaderParameter;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Framework\Jwt\Payload\NestedPayloadInterface;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwtInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class JwtManagerTest extends TestCase
{
    /**
     * @var JwtManagerInterface
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->manager = $objectManager->get(JwtManagerInterface::class);
    }

    /**
     * Verify that the manager is able to create and read token data correctly.
     *
     * @param JwtInterface $jwt
     * @param EncryptionSettingsInterface $encryption
     * @param EncryptionSettingsInterface[] $readEncryption
     * @return void
     *
     * @dataProvider getTokenVariants
     */
    public function testCreateRead(
        JwtInterface $jwt,
        EncryptionSettingsInterface $encryption,
        array $readEncryption
    ): void {
        $token = $this->manager->create($jwt, $encryption);
        $recreated = $this->manager->read($token, $readEncryption);

        //Verifying header
        if ((!$jwt instanceof JwsInterface && !$jwt instanceof JweInterface) || count($jwt->getProtectedHeaders()) == 1) {
            $this->verifyAgainstHeaders([$jwt->getHeader()], $recreated->getHeader());
        }
        //Verifying payload
        $this->assertEquals($jwt->getPayload()->getContent(), $recreated->getPayload()->getContent());
        if ($jwt->getPayload() instanceof ClaimsPayloadInterface) {
            $this->assertInstanceOf(ClaimsPayloadInterface::class, $recreated->getPayload());
        }
        if ($jwt->getPayload() instanceof NestedPayloadInterface) {
            $this->assertInstanceOf(NestedPayloadInterface::class, $recreated->getPayload());
        }

        //JWT type specific validation
        if ($jwt instanceof JwsInterface) {
            $this->assertInstanceOf(JwsInterface::class, $recreated);
            /** @var JwsInterface $recreated */
            if (!$jwt->getUnprotectedHeaders()) {
                $this->assertNull($recreated->getUnprotectedHeaders());
            } else {
                $this->assertTrue(count($recreated->getUnprotectedHeaders()) >= 1);
                $this->verifyAgainstHeaders($jwt->getUnprotectedHeaders(), $recreated->getUnprotectedHeaders()[0]);
            }
            $this->verifyAgainstHeaders($jwt->getProtectedHeaders(), $recreated->getProtectedHeaders()[0]);
        }
        if ($jwt instanceof JweInterface) {
            $this->assertInstanceOf(JweInterface::class, $recreated);
        }
        if ($jwt instanceof UnsecuredJwtInterface) {
            $this->assertInstanceOf(UnsecuredJwtInterface::class, $recreated);
        }

    }

    public function getTokenVariants(): array
    {
        /** @var JwkFactory $jwkFactory */
        $jwkFactory = Bootstrap::getObjectManager()->get(JwkFactory::class);

        $flatJws = new Jws(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('custom-header', 'value'),
                        new PrivateHeaderParameter('another-custom-header', 'value2')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2'),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            ),
            null
        );
        $jwsWithUnprotectedHeader = new Jws(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('custom-header', 'value'),
                        new Critical(['magento'])
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2'),
                    new ExpirationTime(new \DateTimeImmutable())
                ]
            ),
            [
                new JwsHeader(
                    [
                        new PublicHeaderParameter('public-header', 'magento', 'public-value')
                    ]
                )
            ]
        );
        $compactJws = new Jws(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('test', true),
                        new PublicHeaderParameter('test2', 'magento', 'value')
                    ]
                ),
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('test3', true),
                        new PublicHeaderParameter('test4', 'magento', 'value-another')
                    ]
                )
            ],
            new ClaimsPayload([
                new Issuer('magento.com'),
                new JwtId(),
                new Subject('stuff')
            ]),
            [
                new JwsHeader([new PrivateHeaderParameter('public', 'header1')]),
                new JwsHeader([new PrivateHeaderParameter('public2', 'header')])
            ]
        );

        //Keys
        [$rsaPrivate, $rsaPublic] = $this->createRsaKeys();
        $ecKeys = $this->createEcKeys();
        $sharedSecret = random_bytes(128);

        return [
            'jws-HS256' => [
                $flatJws,
                $enc = new JwsSignatureJwks($jwkFactory->createHs256($sharedSecret)),
                [$enc]
            ],
            'jws-HS384' => [
                $flatJws,
                $enc = new JwsSignatureJwks($jwkFactory->createHs384($sharedSecret)),
                [$enc]
            ],
            'jws-HS512' => [
                $jwsWithUnprotectedHeader,
                $enc = new JwsSignatureJwks($jwkFactory->createHs512($sharedSecret)),
                [$enc]
            ],
            'jws-RS256' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignRs256($rsaPrivate, 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyRs256($rsaPublic))]
            ],
            'jws-RS384' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignRs384($rsaPrivate, 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyRs384($rsaPublic))]
            ],
            'jws-RS512' => [
                $jwsWithUnprotectedHeader,
                new JwsSignatureJwks($jwkFactory->createSignRs512($rsaPrivate, 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyRs512($rsaPublic))]
            ],
            'jws-compact-multiple-signatures' => [
                $compactJws,
                new JwsSignatureJwks(
                    new JwkSet(
                        [
                            $jwkFactory->createHs384($sharedSecret),
                            $jwkFactory->createSignRs256($rsaPrivate, 'pass')
                        ]
                    )
                ),
                [
                    new JwsSignatureJwks(
                        new JwkSet(
                            [$jwkFactory->createHs384($sharedSecret), $jwkFactory->createVerifyRs256($rsaPublic)]
                        )
                    )
                ]
            ],
            'jws-compact-multiple-signatures-one-read' => [
                $compactJws,
                new JwsSignatureJwks(
                    new JwkSet(
                        [
                            $jwkFactory->createHs384($sharedSecret),
                            $jwkFactory->createSignRs256($rsaPrivate, 'pass')
                        ]
                    )
                ),
                [new JwsSignatureJwks($jwkFactory->createVerifyRs256($rsaPublic))]
            ],
            'jws-ES256' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignEs256($ecKeys[256][0], 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyEs256($ecKeys[256][1]))]
            ],
            'jws-ES384' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignEs384($ecKeys[384][0], 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyEs384($ecKeys[384][1]))]
            ],
            'jws-ES512' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignEs512($ecKeys[512][0], 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyEs512($ecKeys[512][1]))]
            ],
            'jws-PS256' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignPs256($rsaPrivate, 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyPs256($rsaPublic))]
            ],
            'jws-PS384' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignPs384($rsaPrivate, 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyPs384($rsaPublic))]
            ],
            'jws-PS512' => [
                $flatJws,
                new JwsSignatureJwks($jwkFactory->createSignPs512($rsaPrivate, 'pass')),
                [new JwsSignatureJwks($jwkFactory->createVerifyPs512($rsaPublic))]
            ],
        ];
    }

    private function validateHeader(HeaderInterface $expected, HeaderInterface $actual): void
    {
        if (count($expected->getParameters()) > count($actual->getParameters())) {
            throw new \InvalidArgumentException('Missing header parameters');
        }
        foreach ($expected->getParameters() as $parameter) {
            if ($actual->getParameter($parameter->getName()) === null) {
                throw new \InvalidArgumentException('Missing header parameters');
            }
            if ($actual->getParameter($parameter->getName())->getValue() !== $parameter->getValue()) {
                throw new \InvalidArgumentException('Invalid header data');
            }
        }
    }

    private function verifyAgainstHeaders(array $expected, HeaderInterface $actual): void
    {
        $oneIsValid = false;
        foreach ($expected as $item) {
            try {
                $this->validateHeader($item, $actual);
                $oneIsValid = true;
                break;
            } catch (\InvalidArgumentException $ex) {
                $oneIsValid = false;
            }
        }
        $this->assertTrue($oneIsValid);
    }

    /**
     * Create RSA key-pair.
     *
     * @return string[] With 1st element as private key, second - public.
     */
    private function createRsaKeys(): array
    {
        $rsaPrivateResource = openssl_pkey_new(['private_key_bites' => 512, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($rsaPrivateResource === false) {
            throw new \RuntimeException('Failed to create RSA keypair');
        }
        $rsaPublic = openssl_pkey_get_details($rsaPrivateResource)['key'];
        if (!openssl_pkey_export($rsaPrivateResource, $rsaPrivate, 'pass')) {
            throw new \RuntimeException('Failed to read RSA private key');
        }
        openssl_free_key($rsaPrivateResource);

        return [$rsaPrivate, $rsaPublic];
    }

    /**
     * Create EC key pairs for with different curves.
     *
     * @return array Keys - bits, values contain 2 elements: 0 => private, 1 => public.
     */
    private function createEcKeys(): array
    {
        $curveNameMap = [
            256 => 'prime256v1',
            384 => 'secp384r1',
            512 => 'secp521r1'
        ];
        $ecKeys = [];
        foreach ($curveNameMap as $bits => $curve) {
            $privateResource = openssl_pkey_new(['curve_name' => $curve, 'private_key_type' => OPENSSL_KEYTYPE_EC]);
            if ($privateResource === false) {
                throw new \RuntimeException('Failed to create EC keypair');
            }
            $esPublic = openssl_pkey_get_details($privateResource)['key'];
            if (!openssl_pkey_export($privateResource, $esPrivate, 'pass')) {
                throw new \RuntimeException('Failed to read EC private key');
            }
            openssl_free_key($privateResource);
            $ecKeys[$bits] = [$esPrivate, $esPublic];
            unset($privateResource, $esPublic, $esPrivate);
        }

        return $ecKeys;
    }
}
