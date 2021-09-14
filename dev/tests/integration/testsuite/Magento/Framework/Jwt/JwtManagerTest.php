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
use Magento\Framework\Jwt\Header\KeyId;
use Magento\Framework\Jwt\Header\PrivateHeaderParameter;
use Magento\Framework\Jwt\Header\PublicHeaderParameter;
use Magento\Framework\Jwt\Jwe\Jwe;
use Magento\Framework\Jwt\Jwe\JweEncryptionJwks;
use Magento\Framework\Jwt\Jwe\JweEncryptionSettingsInterface;
use Magento\Framework\Jwt\Jwe\JweHeader;
use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jws\Jws;
use Magento\Framework\Jwt\Jws\JwsHeader;
use Magento\Framework\Jwt\Jws\JwsInterface;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\Payload\ClaimsPayload;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Framework\Jwt\Payload\NestedPayloadInterface;
use Magento\Framework\Jwt\Unsecured\NoEncryption;
use Magento\Framework\Jwt\Unsecured\UnsecuredJwt;
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
        if ((!$jwt instanceof JwsInterface && !$jwt instanceof JweInterface)
            || ($jwt instanceof JwsInterface && count($jwt->getProtectedHeaders()) == 1)
            || ($jwt instanceof JweInterface && !$jwt->getPerRecipientUnprotectedHeaders())
        ) {
            $this->verifyAgainstHeaders([$jwt->getHeader()], $recreated->getHeader());
        }
        if ($readEncryption instanceof JwsSignatureJwks) {
            if ($kid = $readEncryption->getJwkSet()->getKeys()[0]->getKeyId()) {
                $this->assertNotNull($jwt->getHeader()->getParameter('kid'));
                $this->assertEquals($kid, $jwt->getHeader()->getParameter('kid'));
            }
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
        } elseif ($jwt instanceof JweInterface) {
            $this->assertInstanceOf(JweInterface::class, $recreated);
            /** @var JweInterface $recreated */
            if (!$jwt->getPerRecipientUnprotectedHeaders()) {
                $this->assertNull($recreated->getPerRecipientUnprotectedHeaders());
            } else {
                $this->assertTrue(count($recreated->getPerRecipientUnprotectedHeaders()) >= 1);
                $this->verifyAgainstHeaders(
                    $jwt->getPerRecipientUnprotectedHeaders(),
                    $recreated->getPerRecipientUnprotectedHeaders()[0]
                );
            }
            if (!$jwt->getSharedUnprotectedHeader()) {
                $this->assertNull($recreated->getSharedUnprotectedHeader());
            } else {
                $this->verifyAgainstHeaders(
                    [$jwt->getSharedUnprotectedHeader()],
                    $recreated->getSharedUnprotectedHeader()
                );
            }
            $this->verifyAgainstHeaders([$jwt->getProtectedHeader()], $recreated->getProtectedHeader());
            $payload = $jwt->getPayload();
            if ($payload instanceof ClaimsPayloadInterface) {
                foreach ($payload->getClaims() as $claim) {
                    $header = $recreated->getProtectedHeader()->getParameter($claim->getName());
                    if ($claim->isHeaderDuplicated()) {
                        $this->assertNotNull($header);
                        $this->assertEquals($claim->getValue(), $header->getValue());
                    } else {
                        $this->assertNull($header);
                    }
                }
            }
        }
        if ($jwt instanceof UnsecuredJwtInterface) {
            $this->assertInstanceOf(UnsecuredJwtInterface::class, $recreated);
            /** @var UnsecuredJwt $recreated */
            if (!$jwt->getUnprotectedHeaders()) {
                $this->assertNull($recreated->getUnprotectedHeaders());
            } else {
                $this->assertTrue(count($recreated->getUnprotectedHeaders()) >= 1);
                $this->verifyAgainstHeaders($jwt->getUnprotectedHeaders(), $recreated->getUnprotectedHeaders()[0]);
            }
            $this->verifyAgainstHeaders($jwt->getProtectedHeaders(), $recreated->getProtectedHeaders()[0]);
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
        $flatJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            null,
            null,
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $jsonFlatSharedHeaderJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            new JweHeader(
                [
                    new PrivateHeaderParameter('mage', 'test')
                ]
            ),
            null,
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $jsonFlatJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            null,
            [
                new JweHeader(
                    [
                        new PrivateHeaderParameter('mage', 'test')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $jsonJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            new JweHeader(
                [
                    new PrivateHeaderParameter('mage', 'test')
                ]
            ),
            [
                new JweHeader([new PrivateHeaderParameter('tst', 2)]),
                new JweHeader([new PrivateHeaderParameter('test2', 3)])
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $jsonJweKids = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                ]
            ),
            null,
            [
                new JweHeader([new PrivateHeaderParameter('tst', 2), new KeyId('2')]),
                new JweHeader([new PrivateHeaderParameter('test2', 3), new KeyId('1')])
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $flatUnsecured = new UnsecuredJwt(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('test', true),
                        new PublicHeaderParameter('test2', 'magento', 'value')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            ),
            null
        );

        //Keys
        [$rsaPrivate, $rsaPublic] = $this->createRsaKeys();
        $ecKeys = $this->createEcKeys();
        $sharedSecret = random_bytes(2048);

        return [
            'jws-HS256' => [
                $flatJws,
                $enc = new JwsSignatureJwks($jwkFactory->createHs256($sharedSecret)),
                [$enc]
            ],
            'jws-HS384' => [
                $flatJws,
                $enc = new JwsSignatureJwks($jwkFactory->createHs384($sharedSecret, '3')),
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
            'jws-json-multiple-signatures' => [
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
            'jws-json-multiple-signatures-one-read' => [
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
            'jwe-A128KW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createA128KW($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createA128KW($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-A192KW' => [
                $jsonFlatSharedHeaderJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createA192KW($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createA192KW($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-A256KW' => [
                $jsonFlatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createA256KW($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createA256KW($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-multiple-recipients' => [
                $jsonJwe,
                new JweEncryptionJwks(
                    new JwkSet(
                        [
                            $jwkFactory->createA256KW($sharedSecret),
                            $jwkFactory->createA128KW($sharedSecret)
                        ]
                    ),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        new JwkSet(
                            [
                                $jwkFactory->createA256KW($sharedSecret),
                            ]
                        ),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-rsa-oaep' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createEncryptRsaOaep($rsaPublic),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDecryptRsaOaep($rsaPrivate, 'pass'),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-rsa-oaep-256' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createEncryptRsaOaep256($rsaPublic),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDecryptRsaOaep256($rsaPrivate, 'pass'),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192GCM
                    )
                ]
            ],
            'jwe-dir' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createDir(
                        $sharedSecret,
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192_HS384
                    ),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192_HS384
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDir(
                            $sharedSecret,
                            JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192_HS384
                        ),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A192_HS384
                    )
                ]
            ],
            'jwe-multiple-recipients-kids' => [
                $jsonJweKids,
                new JweEncryptionJwks(
                    new JwkSet(
                        [
                            $jwkFactory->createEncryptRsaOaep256($rsaPublic, '2'),
                            $jwkFactory->createA256KW($sharedSecret, '1')
                        ]
                    ),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        new JwkSet(
                            [
                                $jwkFactory->createDecryptRsaOaep256($rsaPrivate, 'pass', '2')
                            ]
                        ),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-ECDH-ES-with-EC' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createEncryptEcdhEsWithEc($ecKeys[256][1]),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDecryptEcdhEsWithEc($ecKeys[256][0], 'pass'),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-ECDH-ES-A128-with-EC' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createEncryptEcdhEsA128kwWithEc($ecKeys[256][1]),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDecryptEcdhEsA128kwWithEc($ecKeys[256][0], 'pass'),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-ECDH-ES-A192-with-EC' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createEncryptEcdhEsA192kwWithEc($ecKeys[256][1]),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDecryptEcdhEsA192kwWithEc($ecKeys[256][0], 'pass'),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-ECDH-ES-A256-with-EC' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createEncryptEcdhEsA256kwWithEc($ecKeys[256][1]),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createDecryptEcdhEsA256kwWithEc($ecKeys[256][0], 'pass'),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128_HS256
                    )
                ]
            ],
            'jwe-A128GCMKW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createA128Gcmkw($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createA128Gcmkw($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                    )
                ]
            ],
            'jwe-A192GCMKW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createA192Gcmkw($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createA192Gcmkw($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                    )
                ]
            ],
            'jwe-A256GCMKW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createA256Gcmkw($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createA256Gcmkw($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                    )
                ]
            ],
            'jwe-PBES2-HS256+A128KW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createPbes2Hs256A128kw($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createPbes2Hs256A128kw($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                    )
                ]
            ],
            'jwe-PBES2-HS384+A192KW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createPbes2Hs384A192kw($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createPbes2Hs384A192kw($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                    )
                ]
            ],
            'jwe-PBES2-HS512+A256KW' => [
                $flatJwe,
                new JweEncryptionJwks(
                    $jwkFactory->createPbes2Hs512A256kw($sharedSecret),
                    JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                ),
                [
                    new JweEncryptionJwks(
                        $jwkFactory->createPbes2Hs512A256kw($sharedSecret),
                        JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
                    )
                ]
            ],
            'unsecured-jwt' => [
                $flatUnsecured,
                new NoEncryption(),
                [new NoEncryption()]
            ]
        ];
    }

    /**
     * Test reading headers.
     *
     * @param JwtInterface $tokenData
     * @param EncryptionSettingsInterface $settings
     * @return void
     *
     * @dataProvider getJwtsForHeaders
     */
    public function testReadHeaders(JwtInterface $tokenData, EncryptionSettingsInterface $settings): void
    {
        $token = $this->manager->create($tokenData, $settings);
        $headers = $this->manager->readHeaders($token);
        /** @var HeaderInterface[] $expectedHeaders */
        $expectedHeaders = [];
        if ($tokenData instanceof JwsInterface) {
            $expectedHeaders = $tokenData->getProtectedHeaders();
            if ($tokenData->getUnprotectedHeaders()) {
                $expectedHeaders = array_merge($expectedHeaders, $tokenData->getUnprotectedHeaders());
            }
        } elseif ($tokenData instanceof JweInterface) {
            $expectedHeaders[] = $tokenData->getProtectedHeader();
            if ($tokenData->getSharedUnprotectedHeader()) {
                $expectedHeaders[] = $tokenData->getSharedUnprotectedHeader();
            }
            if ($tokenData->getPerRecipientUnprotectedHeaders()) {
                $expectedHeaders = array_merge($expectedHeaders, $tokenData->getPerRecipientUnprotectedHeaders());
            }
        } elseif ($tokenData instanceof UnsecuredJwtInterface) {
            $expectedHeaders = $tokenData->getProtectedHeaders();
            if ($tokenData->getUnprotectedHeaders()) {
                $expectedHeaders = array_merge($expectedHeaders, $tokenData->getUnprotectedHeaders());
            }
        }

        foreach ($headers as $header) {
            $this->verifyAgainstHeaders($expectedHeaders, $header);
        }
    }

    public function getJwtsForHeaders(): array
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
        $flatJsonJws = new Jws(
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
        $jsonJws = new Jws(
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
        $flatJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            null,
            null,
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $jsonFlatJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            null,
            [
                new JweHeader(
                    [
                        new PrivateHeaderParameter('mage', 'test')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $jsonJwe = new Jwe(
            new JweHeader(
                [
                    new PrivateHeaderParameter('test', true),
                    new PublicHeaderParameter('test2', 'magento', 'value')
                ]
            ),
            new JweHeader(
                [
                    new PrivateHeaderParameter('mage', 'test')
                ]
            ),
            [
                new JweHeader([new PrivateHeaderParameter('tst', 2)]),
                new JweHeader([new PrivateHeaderParameter('test2', 3)])
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            )
        );
        $flatUnsecured = new UnsecuredJwt(
            [
                new JwsHeader(
                    [
                        new PrivateHeaderParameter('test', true),
                        new PublicHeaderParameter('test2', 'magento', 'value')
                    ]
                )
            ],
            new ClaimsPayload(
                [
                    new PrivateClaim('custom-claim', 'value'),
                    new PrivateClaim('custom-claim2', 'value2', true),
                    new PrivateClaim('custom-claim3', 'value3'),
                    new IssuedAt(new \DateTimeImmutable()),
                    new Issuer('magento.com')
                ]
            ),
            null
        );

        $sharedSecret = random_bytes(2048);
        $jwsJwk = $jwkFactory->createHs256($sharedSecret);
        $jweJwk = $jwkFactory->createA128KW($sharedSecret);
        $jwsSettings = new JwsSignatureJwks($jwsJwk);
        $jsonJwsSettings = new JwsSignatureJwks(new JwkSet([$jwsJwk, $jwsJwk]));
        $jweJwkSettings = new JweEncryptionJwks(
            $jweJwk,
            JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
        );
        $jsonJweSettings = new JweEncryptionJwks(
            new JwkSet([$jweJwk, $jweJwk]),
            JweEncryptionSettingsInterface::CONTENT_ENCRYPTION_ALGO_A128GCM
        );

        return [
            'jws' => [$flatJws, $jwsSettings],
            'flat-jws' => [$flatJsonJws, $jwsSettings],
            'json-jws' => [$jsonJws, $jsonJwsSettings],
            'jwe' => [$flatJwe, $jweJwkSettings],
            'flat-jwe' => [$jsonFlatJwe, $jweJwkSettings],
            'json-jwe' => [$jsonJwe, $jsonJweSettings],
            'none-jws' => [$flatUnsecured, new NoEncryption()]
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
        $this->freeResource($rsaPrivateResource);

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
            $this->freeResource($privateResource);
            $ecKeys[$bits] = [$esPrivate, $esPublic];
            unset($privateResource, $esPublic, $esPrivate);
        }

        return $ecKeys;
    }

    /**
     * @param mixed $resource
     *
     * @return void
     */
    private function freeResource($resource): void
    {
        if (\is_resource($resource) && (version_compare(PHP_VERSION, '8.0') < 0)) {
            openssl_free_key($resource);
        }
    }
}
