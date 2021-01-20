<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Magento\Framework\Jwt\Jwe\JweInterface;
use Magento\Framework\Jwt\Jws\JwsInterface;
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
     */
    public function testCreateRead(JwtInterface $jwt, EncryptionSettingsInterface $encryption): void
    {
        $recreated = $this->manager->read($this->manager->create($jwt, $encryption), [$encryption]);
        if ($jwt instanceof JwsInterface) {
            $this->assertInstanceOf(JwsInterface::class, $recreated);
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
        return [
            'jws-HS256' => [

            ]
        ];
    }
}
