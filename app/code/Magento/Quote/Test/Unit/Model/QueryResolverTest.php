<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;

class QueryResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\QueryResolver
     */
    private $quoteResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->configMock = $this->createMock(\Magento\Framework\App\ResourceConnection\ConfigInterface::class);
        $this->cacheMock = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializer = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->quoteResolver = new \Magento\Quote\Model\QueryResolver(
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
    public function isSingleQueryWhenDataNotCachedDataProvider()
    {
        return [
            ['default', true],
            ['checkout', false],
        ];
    }
}
