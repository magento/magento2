<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Test\Unit\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\IdentityResolverInterface;
use Magento\GraphQlCache\Model\CacheableQueryHandler;
use Magento\GraphQlCache\Model\IdentityResolverPool;
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

    private $identityResolverPoolMock;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $this->cacheableQueryMock = $this->createMock(CacheableQuery::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->identityResolverPoolMock = $this->createMock(IdentityResolverPool::class);
        $this->cacheableQueryHandler = $objectManager->getObject(
            CacheableQueryHandler::class,
            [
                'cacheableQuery' => $this->cacheableQueryMock,
                'request' => $this->requestMock,
                'identityResolverPool' => $this->identityResolverPoolMock
            ]
        );
    }

    /**
     * @param array $resolvedData
     * @param array $resolvedIdentities
     * @dataProvider resolvedDataProvider
     */
    public function testhandleCacheFromResolverResponse(
        array $resolvedData,
        array $resolvedIdentities,
        array $expectedCacheTags
    ): void {
        $cacheData = [
            'cacheIdentityResolver' => IdentityResolverInterface::class,
            'cacheTag' => 'cat_p'
        ];
        $fieldMock = $this->createMock(Field::class);
        $mockIdentityResolver = $this->getMockBuilder($cacheData['cacheIdentityResolver'])
            ->setMethods(['getIdentifiers'])
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())->method('isGet')->willReturn(true);
        $this->identityResolverPoolMock->expects($this->once())->method('get')->willReturn($mockIdentityResolver);
        $fieldMock->expects($this->once())->method('getCache')->willReturn($cacheData);
        $mockIdentityResolver->expects($this->once())
            ->method('getIdentifiers')
            ->with($resolvedData)
            ->willReturn($resolvedIdentities);
        $this->cacheableQueryMock->expects($this->once())->method('addCacheTags')->with($expectedCacheTags);
        $this->cacheableQueryMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->cacheableQueryMock->expects($this->once())->method('setCacheValidity')->with(true);

        $this->cacheableQueryHandler->handleCacheFromResolverResponse($resolvedData, $fieldMock);
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
                "resolvedIdentities" => [10],
                "expectedCacheTags" => ["cat_p", "cat_p_10"]
            ]
        ];
    }
}
