<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductTypes;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config
     */
    private $config;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->readerMock = $this->getMock(
            \Magento\Catalog\Model\ProductTypes\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->mockObjectManager(
            [\Magento\Framework\Serialize\SerializerInterface::class => $this->serializerMock]
        );
    }

    protected function tearDown()
    {
        $reflectionProperty = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    /**
     * @param array $value
     * @param mixed $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType($value, $expected)
    {
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->willReturn('serializedData');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($value);

        $this->config = $this->objectManager->getObject(
            \Magento\Catalog\Model\ProductTypes\Config::class,
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
            ]
        );
        $this->assertEquals($expected, $this->config->getType('global'));
    }

    public function getTypeDataProvider()
    {
        return [
            'global_key_exist' => [['types' => ['global' => 'value']], 'value'],
            'return_default_value' => [['types' => ['some_key' => 'value']], []]
        ];
    }

    public function testGetAll()
    {
        $expected = ['Expected Data'];
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(json_encode('"types":["Expected Data"]]'));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn(['types' => $expected]);

        $this->config = $this->objectManager->getObject(
            \Magento\Catalog\Model\ProductTypes\Config::class,
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
            ]
        );
        $this->assertEquals($expected, $this->config->getAll());
    }

    public function testIsProductSet()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn([]);

        $this->config = $this->objectManager->getObject(
            \Magento\Catalog\Model\ProductTypes\Config::class,
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
            ]
        );
        $this->assertEquals(false, $this->config->isProductSet('typeId'));
    }

    /**
     * Mock application object manager to return configured dependencies.
     *
     * @param array $dependencies
     * @return void
     */
    private function mockObjectManager($dependencies)
    {
        $dependencyMap = [];
        foreach ($dependencies as $type => $instance) {
            $dependencyMap[] = [$type, $instance];
        }
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($dependencyMap));
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }
}
