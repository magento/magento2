<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Test\Unit\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\GraphQlCache\Model\CacheableQueryHandler;
use Magento\GraphQlCache\Model\Resolver\IdentityPool;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test CacheableQueryHandler
 */
class CacheableQueryHandlerTest extends TestCase
{

    private $cacheableQueryHandler;

    private $cacheableQueryMock;

    private $requestMock;

    private $identityPoolMock;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $this->cacheableQueryMock = $this->createMock(CacheableQuery::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->identityPoolMock = $this->createMock(IdentityPool::class);
        $this->cacheableQueryHandler = $objectManager->getObject(
            CacheableQueryHandler::class,
            [
                'cacheableQuery' => $this->cacheableQueryMock,
                'request' => $this->requestMock,
                'identityPool' => $this->identityPoolMock
            ]
        );
    }

    /**
     * @param array $resolvedData
     * @param array $identities
     * @dataProvider resolvedDataProvider
     */
    public function testhandleCacheFromResolverResponse(
        array $resolvedData,
        array $identities,
        array $expectedCacheTags
    ): void {
        $cacheData = [
            'cacheIdentity' => IdentityInterface::class,
            'cacheTag' => 'cat_p'
        ];
        $mockIdentity = $this->getMockBuilder($cacheData['cacheIdentity'])
            ->setMethods(['getIdentities'])
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())->method('isGet')->willReturn(true);
        $this->identityPoolMock->expects($this->once())->method('get')->willReturn($mockIdentity);
        $mockIdentity->expects($this->once())
            ->method('getIdentities')
            ->with($resolvedData)
            ->willReturn($identities);
        $this->cacheableQueryMock->expects($this->once())->method('addCacheTags')->with($expectedCacheTags);
        $this->cacheableQueryMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->cacheableQueryMock->expects($this->once())->method('setCacheValidity')->with(true);

        $this->cacheableQueryHandler->handleCacheFromResolverResponse($resolvedData, $cacheData);
    }

    /**
     * @return array
     */
    public function resolvedDataProvider(): array
    {
        return [
            [
                "resolvedData" => [
                    "id" => 10,
                    "name" => "TesName",
                    "sku" => "TestSku"
                ],
                "identities" => ["cat_p", "cat_p_10"],
                "expectedCacheTags" => ["cat_p", "cat_p_10"]
            ]
        ];
    }
}
