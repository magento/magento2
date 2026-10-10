<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\Js;

use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for cookie domain handling in JS cookie configuration block.
 *
 * Verifies that configured cookie domains are passed through to the frontend
 * without modification (no leading dot prepended).
 *
 * @see https://github.com/magento/magento2/issues/40515
 */
#[AppArea('frontend')]
class CookieDomainTest extends TestCase
{
    /**
     * Verify that a configured cookie domain is returned without a leading dot.
     *
     * Per RFC 6265, browsers handle domain matching for cookies without a leading dot
     * the same as with one. Prepending a dot causes cross-subdomain cookie collision
     * in multistore setups with sibling domains.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        ConfigFixture('web/cookie/cookie_domain', 'example.com', 'store'),
    ]
    public function testGetDomainDoesNotPrependDot(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Cookie $block */
        $block = $objectManager->create(Cookie::class);

        $this->assertSame(
            'example.com',
            $block->getDomain(),
            'Cookie domain must not have a leading dot prepended'
        );
    }

    /**
     * Verify that a subdomain cookie domain is returned without a leading dot.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        ConfigFixture('web/cookie/cookie_domain', 'sub.example.com', 'store'),
    ]
    public function testGetDomainPreservesSubdomain(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Cookie $block */
        $block = $objectManager->create(Cookie::class);

        $this->assertSame(
            'sub.example.com',
            $block->getDomain(),
            'Subdomain cookie domain must be returned as-is'
        );
    }

    /**
     * Verify that an IP address cookie domain is returned unchanged.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        ConfigFixture('web/cookie/cookie_domain', '127.0.0.1', 'store'),
    ]
    public function testGetDomainPreservesIpAddress(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Cookie $block */
        $block = $objectManager->create(Cookie::class);

        $this->assertSame(
            '127.0.0.1',
            $block->getDomain(),
            'IP address cookie domain must be returned unchanged'
        );
    }

    /**
     * Verify that a domain already having a leading dot is returned as-is.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        ConfigFixture('web/cookie/cookie_domain', '.example.com', 'store'),
    ]
    public function testGetDomainPreservesExistingLeadingDot(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Cookie $block */
        $block = $objectManager->create(Cookie::class);

        $this->assertSame(
            '.example.com',
            $block->getDomain(),
            'Domain with existing leading dot must be returned as-is'
        );
    }
}
