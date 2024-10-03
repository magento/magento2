<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Store\Model\ScopeResolver;

/**
 * Test for ScopeResolver
 */
class ScopeResolverTest extends TestCase
{
    /**
     * @var ScopeTreeProviderInterface|MockObject
     */
    private $scopeTreeMock;

    /**
     * @var ScopeResolver
     */
    private $scopeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeTreeMock = $this->getMockBuilder(ScopeTreeProviderInterface::class)
            ->getMockForAbstractClass();
        $this->scopeResolver = new ScopeResolver($this->scopeTreeMock);
    }

    /**
     * Check is some scope belongs to other scope
     *
     * @param string $baseScope
     * @param int $baseScopeId
     * @param string $requestedScope
     * @param int $requestedScopeId
     * @param bool $isBelong
     * @dataProvider testIsBelongsToScopeDataProvider
     */
    public function testIsBelongsToScope(
        string $baseScope,
        int $baseScopeId,
        string $requestedScope,
        int $requestedScopeId,
        bool $isBelong
    ) {
        $this->scopeTreeMock->expects($this->any())
            ->method('get')
            ->willReturn(
                $this->getTree()
            );
        $this->assertEquals(
            $isBelong,
            $this->scopeResolver->isBelongsToScope($baseScope, $baseScopeId, $requestedScope, $requestedScopeId)
        );
    }

    /**
     * Data provider for testIsBelongsToScope
     *
     * @return array[]
     */
    public static function testIsBelongsToScopeDataProvider()
    {
        return [
            'All scopes belongs to Default' => [
                'baseScope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                'baseScopeId' => 0,
                'requestedScope' => ScopeInterface::SCOPE_WEBSITE,
                'requestedScopeId' => 1,
                'isBelong' => true
            ],
            'Store group belongs to website' => [
                'baseScope' => ScopeInterface::SCOPE_WEBSITE,
                'baseScopeId' => 1,
                'requestedScope' => ScopeInterface::SCOPE_GROUP,
                'requestedScopeId' => 1,
                'isBelong' => true
            ],
            'Store belongs to store group' => [
                'baseScope' => ScopeInterface::SCOPE_GROUP,
                'baseScopeId' => 1,
                'requestedScope' => ScopeInterface::SCOPE_STORE,
                'requestedScopeId' => 1,
                'isBelong' => true
            ],
            'Store belongs to website' => [
                'baseScope' => ScopeInterface::SCOPE_WEBSITE,
                'baseScopeId' => 1,
                'requestedScope' => ScopeInterface::SCOPE_STORE,
                'requestedScopeId' => 1,
                'isBelong' => true
            ],
            'Store group not belongs to website' => [
                'baseScope' => ScopeInterface::SCOPE_WEBSITE,
                'baseScopeId' => 1,
                'requestedScope' => ScopeInterface::SCOPE_GROUP,
                'requestedScopeId' => 2,
                'isBelong' => false
            ],
            'Store not belongs to store group' => [
                'baseScope' => ScopeInterface::SCOPE_GROUP,
                'baseScopeId' => 1,
                'requestedScope' => ScopeInterface::SCOPE_STORE,
                'requestedScopeId' => 2,
                'isBelong' => false
            ],
            'Store not belongs to website' => [
                'baseScope' => ScopeInterface::SCOPE_WEBSITE,
                'baseScopeId' => 1,
                'requestedScope' => ScopeInterface::SCOPE_STORE,
                'requestedScopeId' => 2,
                'isBelong' => false
            ],
        ];
    }

    /**
     * Get scope tree with 2 websites, 2 groups and 2 stores
     *
     * @return array
     */
    private function getTree()
    {
        return [
            'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'scope_id' => null,
            'scopes' => [
                [
                    'scope' => ScopeInterface::SCOPE_WEBSITE,
                    'scope_id' => 1,
                    'scopes' => [
                        [
                            'scope' => ScopeInterface::SCOPE_GROUP,
                            'scope_id' => 1,
                            'scopes' => [
                                [
                                    'scope' => ScopeInterface::SCOPE_STORE,
                                    'scope_id' => 1,
                                    'scopes' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'scope' => ScopeInterface::SCOPE_WEBSITE,
                    'scope_id' => 2,
                    'scopes' => [
                        [
                            'scope' => ScopeInterface::SCOPE_GROUP,
                            'scope_id' => 2,
                            'scopes' => [
                                [
                                    'scope' => ScopeInterface::SCOPE_STORE,
                                    'scope_id' => 2,
                                    'scopes' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
