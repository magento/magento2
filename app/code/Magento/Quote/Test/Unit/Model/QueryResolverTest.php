<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

class QueryResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\QueryResolver
     */
    protected $quoteResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\ResourceConnection\ConfigInterface');
        $this->cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
        $this->quoteResolver = new \Magento\Quote\Model\QueryResolver(
            $this->configMock,
            $this->cacheMock,
            'connection_config_cache'
        );

    }

    public function testIsSingleQueryWhenDataWereCached()
    {
        $queryData['checkout'] = true;
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with('connection_config_cache')
            ->willReturn(serialize($queryData));
        $this->assertTrue($this->quoteResolver->isSingleQuery());
    }

    public function testIsSingleQueryWhenDataNotCached()
    {
        $queryData['checkout'] = true;
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with('connection_config_cache')
            ->willReturn(false);
        $this->configMock
            ->expects($this->once())
            ->method('getConnectionName')
            ->with('checkout_setup')
            ->willReturn('default');
        $this->cacheMock
            ->expects($this->once())
            ->method('save')
            ->with(serialize($queryData), 'connection_config_cache', []);
        $this->assertTrue($this->quoteResolver->isSingleQuery());
    }

    public function testIsSingleQueryWhenSeveralConnectionsExist()
    {
        $queryData['checkout'] = false;
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with('connection_config_cache')
            ->willReturn(false);
        $this->configMock
            ->expects($this->once())
            ->method('getConnectionName')
            ->with('checkout_setup')
            ->willReturn('checkout');
        $this->cacheMock
            ->expects($this->once())
            ->method('save')
            ->with(serialize($queryData), 'connection_config_cache', []);
        $this->assertFalse($this->quoteResolver->isSingleQuery());
    }
}
