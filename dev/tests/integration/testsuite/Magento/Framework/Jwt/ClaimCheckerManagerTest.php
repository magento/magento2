<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Magento\Framework\Jwt\Fixtures\IssuerChecker;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Contains scenarios for testing claims validation.
 */
class ClaimCheckerManagerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Checks claims validation
     */
    public function testCheck(): void
    {
        $claims = [
            'iss' => 'dev',
            'iat' => 1561564372,
            'exp' => 1593100372,
            'aud' => 'dev',
            'sub' => 'test',
            'key' => 'value'
        ];

        /** @var ClaimCheckerManager $claimCheckerManager */
        $claimCheckerManager = $this->objectManager->create(
            ClaimCheckerManager::class,
            [
                'checkers' => [
                    IssuerChecker::class,
                    ExpirationTimeChecker::class,
                    IssuedAtChecker::class
                ],
                'mandatoryClaims' => ['iss', 'iat', 'exp']
            ]
        );

        $checked = $claimCheckerManager->check($claims);
        self::assertEquals(
            [
                'iss' => 'dev',
                'iat' => 1561564372,
                'exp' => 1593100372,
            ],
            $checked
        );
    }

    /**
     * Checks a case when mandatory claims are missed.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following claims are mandatory: iss.
     */
    public function testCheckMissedClaims(): void
    {
        $claims = [
            'iat' => 1561564372,
            'exp' => 1593100372,
        ];

        /** @var ClaimCheckerManager $claimCheckerManager */
        $claimCheckerManager = $this->objectManager->create(
            ClaimCheckerManager::class,
            [
                'checkers' => [
                    IssuerChecker::class,
                ],
                'mandatoryClaims' => ['iss']
            ]
        );

        $claimCheckerManager->check($claims);
    }

    /**
     * Checks a case when token is expired.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The token expired.
     */
    public function testCheckExpiredClaims()
    {
        $claims = [
            'iat' => 1561564372,
            'exp' => 1561564380,
        ];

        /** @var ClaimCheckerManager $claimCheckerManager */
        $claimCheckerManager = $this->objectManager->create(
            ClaimCheckerManager::class,
            [
                'checkers' => [
                    ExpirationTimeChecker::class,
                ]
            ]
        );

        $claimCheckerManager->check($claims);
    }
}
