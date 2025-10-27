<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\App\ObjectManager\ConfigCache;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigCacheTest extends TestCase
{
    /**
     * @var ConfigCache
     */
    private $configCache;

    /**
     * @var ConfigCache|MockObject
     */
    private $cacheFrontendMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->cacheFrontendMock = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->configCache = $objectManagerHelper->getObject(
            ConfigCache::class,
            ['cacheFrontend' => $this->cacheFrontendMock]
        );

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->configCache,
            'serializer',
            $this->serializerMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->configCache);
    }

    /**
     * @param $data
     * @param $expectedResult
     * @param $unserializeCalledNum
     * @dataProvider getDataProvider
     */
    public function testGet($data, $expectedResult, $unserializeCalledNum = 1)
    {
        $key = 'key';
        $this->cacheFrontendMock->expects($this->once())
            ->method('load')
            ->with('diConfig' . $key)
            ->willReturn($data);
        $this->serializerMock->expects($this->exactly($unserializeCalledNum))
            ->method('unserialize')
            ->with($data)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->configCache->get($key));
    }

    /**
     * @return array
     */
    public static function getDataProvider()
    {
        return [
            [false, false, 0],
            ['serialized data', ['some data']],
        ];
    }

    public function testSave()
    {
        $key = 'key';
        $config = ['config'];
        $serializedData = 'serialized data';
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn($serializedData);
        $this->cacheFrontendMock->expects($this->once())->method('save')->with($serializedData, 'diConfig' . $key);
        $this->configCache->save($config, $key);
    }
}
