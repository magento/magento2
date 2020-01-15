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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\QueryResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryResolverTest extends TestCase
{
    /**
     * @var \Magento\Quote\Model\QueryResolver
     */
    private $model;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            QueryResolver::class,
            [
                'config' => $this->configMock,
                'cache' => $this->cacheMock,
                'serializer' => $this->serializerMock,
                'cacheId' => 'connection_config_cache'
            ]
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
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->assertTrue($this->model->isSingleQuery());
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
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->configMock
            ->expects($this->once())
            ->method('getConnectionName')
            ->with('checkout_setup')
            ->willReturn($connectionName);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->cacheMock
            ->expects($this->once())
            ->method('save')
            ->with($serializedData, 'connection_config_cache', []);
        $this->assertEquals($isSingleQuery, $this->model->isSingleQuery());
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
