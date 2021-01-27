<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\Framework\Jwt\Header\PrivateHeaderParameter;
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
     * @return void
     *
     * @dataProvider getTokenVariants
     */
    public function testCreateRead(JwtInterface $jwt, EncryptionSettingsInterface $encryption): void
    {
        $token = $this->manager->create($jwt, $encryption);
        $recreated = $this->manager->read($token, [$encryption]);

        //Verifying header
        $this->verifyHeader($jwt->getHeader(), $recreated->getHeader());
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
                $this->verifyHeader($jwt->getUnprotectedHeaders()[0], $recreated->getUnprotectedHeaders()[0]);
            }
            $this->verifyHeader($jwt->getProtectedHeaders()[0], $recreated->getProtectedHeaders()[0]);
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

        $hmacFlatJws = new Jws(
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
                    new PrivateClaim('custom-claim3', 'value3')
                ]
            ),
            null
        );

        return [
            'jws-HS256' => [
                $hmacFlatJws,
                new JwsSignatureJwks($jwkFactory->createHs256(random_bytes(128)))
            ],
            'jws-HS384' => [
                $hmacFlatJws,
                new JwsSignatureJwks($jwkFactory->createHs384(random_bytes(128)))
            ],
            'jws-HS512' => [
                $hmacFlatJws,
                new JwsSignatureJwks($jwkFactory->createHs512(random_bytes(128)))
            ]
        ];
    }

    private function verifyHeader(HeaderInterface $expected, HeaderInterface $actual): void
    {
        $this->assertTrue(
            count($expected->getParameters()) <= count($actual->getParameters())
        );
        foreach ($expected->getParameters() as $parameter) {
            $this->assertNotNull($actual->getParameter($parameter->getName()));
            $this->assertEquals(
                $parameter->getValue(),
                $actual->getParameter($parameter->getName())->getValue()
            );
        }
    }
}
