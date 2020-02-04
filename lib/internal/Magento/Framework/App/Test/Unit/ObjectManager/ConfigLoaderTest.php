<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\Serialize\SerializerInterface;

class ConfigLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    private $object;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\DomFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerFactoryMock;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->readerMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class);

        $this->readerFactoryMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\Config\Reader\DomFactory::class, ['create']);

        $this->readerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->readerMock));

        $this->cacheMock = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->object = $objectManagerHelper->getObject(
            \Magento\Framework\App\ObjectManager\ConfigLoader::class,
            [
                'cache' => $this->cacheMock,
                'readerFactory' => $this->readerFactoryMock,
            ]
        );
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'serializer',
            $this->serializerMock
        );
    }

    /**
     * @param $area
     * @dataProvider loadDataProvider
     */
    public function testLoadNotCached($area)
    {
        $configData = ['some' => 'config', 'data' => 'value'];
        $serializedData = 'serialized data';

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($area . '::DiConfig')
            ->will($this->returnValue(false));

        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData);
        $this->readerMock->expects($this->once())
            ->method('read')
            ->with($area)
            ->will($this->returnValue($configData));

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn($serializedData);

        $this->serializerMock->expects($this->never())->method('unserialize');

        $this->assertEquals($configData, $this->object->load($area));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            'global files' => ['global'],
            'adminhtml files' => ['adminhtml'],
            'any area files' => ['any']
        ];
    }

    public function testLoadCached()
    {
        $configData = ['some' => 'config', 'data' => 'value'];
        $serializedData = 'serialized data';

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($serializedData);
        $this->cacheMock->expects($this->never())
            ->method('save');
        $this->readerMock->expects($this->never())->method('read');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($configData);
        $this->serializerMock->expects($this->never())->method('serialize');
        $this->assertEquals($configData, $this->object->load('testArea'));
    }
}
