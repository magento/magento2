<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Collection\Db\FetchStrategy;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategy\Cache;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache
     */
    private $fetchStrategyCache;

    /**
     * @var FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fetchStrategyMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->selectMock = $this->getMock(Select::class, ['assemble'], [], '', false);
        $this->selectMock->expects($this->once())
            ->method('assemble')
            ->willReturn('SELECT * FROM fixture_table');
        $this->cacheMock = $this->getMock(FrontendInterface::class);
        $this->fetchStrategyMock = $this->getMock(FetchStrategyInterface::class);
        $this->serializerMock = $this->getMock(SerializerInterface::class);
        $this->fetchStrategyCache = (new ObjectManager($this))->getObject(
            Cache::class,
            [
                'cache' => $this->cacheMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'cacheIdPrefix' => 'fixture_',
                'cacheTags' => ['fixture_tag_one', 'fixture_tag_two'],
                'cacheLifetime' => 86400,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testFetchCached()
    {
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('fixture_06a6b0cfd83bf997e76b1b403df86569')
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->fetchStrategyMock->expects($this->never())
            ->method('fetchAll');
        $this->cacheMock->expects($this->never())
            ->method('save');
        $this->assertEquals(
            $data,
            $this->fetchStrategyCache->fetchAll($this->selectMock, [])
        );
    }

    public function testFetchNotCached()
    {
        $cacheId = 'fixture_06a6b0cfd83bf997e76b1b403df86569';
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';
        $bindParams = [
            'param_one' => 'value_one',
            'param_two' => 'value_two'
        ];
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheId)
            ->willReturn(false);
        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->with(
                $this->selectMock,
                $bindParams
            )
            ->willReturn($data);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                $cacheId,
                ['fixture_tag_one', 'fixture_tag_two'],
                86400
            );
        $this->assertEquals(
            $data,
            $this->fetchStrategyCache->fetchAll($this->selectMock, $bindParams)
        );
    }
}
