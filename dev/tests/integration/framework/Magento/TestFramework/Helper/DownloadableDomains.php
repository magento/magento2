<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Helper;

use Magento\Downloadable\Api\DomainManagerInterface;

class DownloadableDomains
{
    public const DOMAINS = [
        'example.com',
        'www.example.com',
        'www.sample.example.com',
        'google.com',
        'sampleurl.com',
    ];

    /**
     * Keep the Web API baseline across fixture object manager reinitializations.
     *
     * @var string[]
     */
    private static array $persistentDomains = [];

    /**
     * Preserve domains across Web API fixture cleanup.
     *
     * @param string[] $domains
     * @return void
     */
    public static function setPersistentDomains(array $domains): void
    {
        self::$persistentDomains = array_map('strtolower', $domains);
    }

    /**
     * Add missing fixture domains without rewriting an unchanged allowlist.
     *
     * @param DomainManagerInterface $domainManager
     * @param string[] $domains
     * @return void
     */
    public static function addDomains(DomainManagerInterface $domainManager, array $domains): void
    {
        $domains = array_values(array_diff(array_map('strtolower', $domains), $domainManager->getDomains()));
        if ($domains) {
            $domainManager->addDomains($domains);
        }
    }

    /**
     * Remove fixture domains while retaining the Web API baseline.
     *
     * @param DomainManagerInterface $domainManager
     * @param string[] $domains
     * @return void
     */
    public static function removeDomains(DomainManagerInterface $domainManager, array $domains): void
    {
        $domains = array_values(array_diff(array_map('strtolower', $domains), self::$persistentDomains));
        if ($domains) {
            $domainManager->removeDomains($domains);
        }
    }
}
