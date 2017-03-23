<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @dataProvider getDataProvider
     */
    public function testGet($loadData, $expectedResult)
    {
        $key = 'key';
        $this->cacheFrontendMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'diConfig' . $key
        )->will(
            $this->returnValue($loadData)
        );
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($loadData)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->configCache->get($key));
    }

    public function getDataProvider()
    {
        return [
            [false, false],
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
