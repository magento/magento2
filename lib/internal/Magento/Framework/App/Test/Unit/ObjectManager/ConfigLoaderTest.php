<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Config\Reader\DomFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    /**
     * @var ConfigLoader
     */
    private $object;

    /**
     * @var DomFactory|MockObject
     */
    private $readerFactoryMock;

    /**
     * @var Dom|MockObject
     */
    private $readerMock;

    /**
     * @var Config|MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(Dom::class);

        $this->readerFactoryMock =
            $this->createPartialMock(DomFactory::class, ['create']);

        $this->readerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->readerMock);

        $this->cacheMock = $this->createMock(Config::class);

        $objectManagerHelper = new ObjectManager($this);

        $this->object = $objectManagerHelper->getObject(
            ConfigLoader::class,
            [
                'cache' => $this->cacheMock,
                'readerFactory' => $this->readerFactoryMock,
            ]
        );
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
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
            ->willReturn(false);

        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData);
        $this->readerMock->expects($this->once())
            ->method('read')
            ->with($area)
            ->willReturn($configData);

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
    public static function loadDataProvider()
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
