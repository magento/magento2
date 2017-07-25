<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\Serialize\SerializerInterface;

class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigCache
     */
    private $configCache;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheFrontendMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheFrontendMock = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->configCache = $objectManagerHelper->getObject(
            \Magento\Framework\App\ObjectManager\ConfigCache::class,
            ['cacheFrontend' => $this->cacheFrontendMock]
        );

        $this->serializerMock = $this->getMock(SerializerInterface::class);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->configCache,
            'serializer',
            $this->serializerMock
        );
    }

    protected function tearDown()
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
    public function getDataProvider()
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
