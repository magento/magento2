<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QueryResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryResolverTest extends TestCase
{
    /**
     * @var QueryResolver
     */
    private $quoteResolver;

    /**
     * @var MockObject
     */
    private $configMock;

    /**
     * @var MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->quoteResolver = new QueryResolver(
            $this->configMock,
            $this->cacheMock,
            'connection_config_cache',
            $this->serializer
        );
    }

    public function testIsSingleQueryWhenDataWereCached()
    {
        $serializedData = '{"checkout":true}';
        $data = ['checkout' => true];
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with('connection_config_cache')
            ->willReturn($serializedData);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->assertTrue($this->quoteResolver->isSingleQuery());
    }

    /**
     * @param string $connectionName
     * @param bool $isSingleQuery
     *
     * @dataProvider isSingleQueryWhenDataNotCachedDataProvider
     */
    public function testIsSingleQueryWhenDataNotCached($connectionName, $isSingleQuery)
    {
        $data = ['checkout' => $isSingleQuery];
        $serializedData = '{"checkout":true}';
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with('connection_config_cache')
            ->willReturn(false);
        $this->serializer->expects($this->never())
            ->method('unserialize');
        $this->configMock
            ->expects($this->once())
            ->method('getConnectionName')
            ->with('checkout_setup')
            ->willReturn($connectionName);
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->cacheMock
            ->expects($this->once())
            ->method('save')
            ->with($serializedData, 'connection_config_cache', []);
        $this->assertEquals($isSingleQuery, $this->quoteResolver->isSingleQuery());
    }

    /**
     * @return array
     */
    public static function isSingleQueryWhenDataNotCachedDataProvider()
    {
        return [
            ['default', true],
            ['checkout', false],
        ];
    }
}
