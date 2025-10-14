<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\ScopeTypeNormalizer;
use PHPUnit\Framework\TestCase;

class ScopeTypeNormalizerTest extends TestCase
{
    /**
     * @var ScopeTypeNormalizer
     */
    private $scopeTypeNormalizer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeTypeNormalizer = new ScopeTypeNormalizer();
    }

    /**
     * @param string $scopeType
     * @param bool $plural
     * @param string $expectedResult
     * @dataProvider normalizeDataProvider
     */
    public function testNormalize($scopeType, $plural, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->scopeTypeNormalizer->normalize($scopeType, $plural));
    }

    /**
     * @return array
     */
    public static function normalizeDataProvider()
    {
        return [
            [ScopeInterface::SCOPE_WEBSITE, true, ScopeInterface::SCOPE_WEBSITES],
            [ScopeInterface::SCOPE_WEBSITES, true, ScopeInterface::SCOPE_WEBSITES],
            [ScopeInterface::SCOPE_WEBSITE, false, ScopeInterface::SCOPE_WEBSITE],
            [ScopeInterface::SCOPE_WEBSITES, false, ScopeInterface::SCOPE_WEBSITE],
            [ScopeInterface::SCOPE_GROUP, true, ScopeInterface::SCOPE_GROUPS],
            [ScopeInterface::SCOPE_GROUPS, true, ScopeInterface::SCOPE_GROUPS],
            [ScopeInterface::SCOPE_GROUP, false, ScopeInterface::SCOPE_GROUP],
            [ScopeInterface::SCOPE_GROUPS, false, ScopeInterface::SCOPE_GROUP],
            [ScopeInterface::SCOPE_STORE, true, ScopeInterface::SCOPE_STORES],
            [ScopeInterface::SCOPE_STORES, true, ScopeInterface::SCOPE_STORES],
            [ScopeInterface::SCOPE_STORE, false, ScopeInterface::SCOPE_STORE],
            [ScopeInterface::SCOPE_STORES, false, ScopeInterface::SCOPE_STORE],
            ['default', true, 'default'],
            ['default', false, 'default'],
        ];
    }

    /**
     * @param string $scopeType
     * @param string $expectedResult
     * @dataProvider normalizeDefaultDataProvider
     */
    public function testNormalizeDefault($scopeType, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->scopeTypeNormalizer->normalize($scopeType));
    }

    /**
     * @return array
     */
    public static function normalizeDefaultDataProvider()
    {
        return [
            [ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES],
            [ScopeInterface::SCOPE_WEBSITES, ScopeInterface::SCOPE_WEBSITES],
            [ScopeInterface::SCOPE_GROUP, ScopeInterface::SCOPE_GROUPS],
            [ScopeInterface::SCOPE_GROUPS, ScopeInterface::SCOPE_GROUPS],
            [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES],
            [ScopeInterface::SCOPE_STORES, ScopeInterface::SCOPE_STORES],
            ['default', 'default'],
        ];
    }
}
