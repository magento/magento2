<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Test\Unit;

use \Magento\Framework\Image\AdapterFactory;

class AdapterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Image\Adapter\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->createPartialMock(
            \Magento\Framework\Image\Adapter\ConfigInterface::class,
            ['getAdapterAlias', 'getAdapters']
        );

        $this->configMock->expects(
            $this->once()
        )->method(
            'getAdapters'
        )->will(
            $this->returnValue(
                [
                    'GD2' => ['class' => \Magento\Framework\Image\Adapter\Gd2::class],
                    'IMAGEMAGICK' => ['class' => \Magento\Framework\Image\Adapter\ImageMagick::class],
                    'wrongInstance' => ['class' => 'stdClass'],
                    'test' => [],
                ]
            )
        );
    }

    /**
     * @dataProvider createDataProvider
     * @param string $alias
     * @param string $class
     */
    public function testCreate($alias, $class)
    {
        $objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $imageAdapterMock = $this->createPartialMock($class, ['checkDependencies']);
        $imageAdapterMock->expects($this->once())->method('checkDependencies');

        $objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $class
        )->will(
            $this->returnValue($imageAdapterMock)
        );

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $imageAdapter = $adapterFactory->create($alias);
        $this->assertInstanceOf($class, $imageAdapter);
    }

    /**
     * @see self::testCreate()
     * @return array
     */
    public function createDataProvider()
    {
        return [
            ['GD2', \Magento\Framework\Image\Adapter\Gd2::class],
            ['IMAGEMAGICK', \Magento\Framework\Image\Adapter\ImageMagick::class]
        ];
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     */
    public function testCreateWithoutName()
    {
        $adapterAlias = 'IMAGEMAGICK';
        $adapterClass = \Magento\Framework\Image\Adapter\ImageMagick::class;

        $this->configMock->expects($this->once())->method('getAdapterAlias')->will($this->returnValue($adapterAlias));

        $objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $imageAdapterMock = $this->createPartialMock($adapterClass, ['checkDependencies']);
        $imageAdapterMock->expects($this->once())->method('checkDependencies');

        $objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $adapterClass
        )->will(
            $this->returnValue($imageAdapterMock)
        );

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $imageAdapter = $adapterFactory->create();
        $this->assertInstanceOf($adapterClass, $imageAdapter);
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Image adapter is not selected.
     */
    public function testInvalidArgumentException()
    {
        $this->configMock->expects($this->once())->method('getAdapterAlias')->will($this->returnValue(''));
        $objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $adapterFactory->create();
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Image adapter for 'test' is not setup.
     */
    public function testNonAdapterClass()
    {
        $alias = 'test';
        $objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $adapterFactory->create($alias);
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass is not instance of \Magento\Framework\Image\Adapter\AdapterInterface
     */
    public function testWrongInstance()
    {
        $alias = 'wrongInstance';
        $class = 'stdClass';
        $objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $imageAdapterMock = $this->createPartialMock($class, ['checkDependencies']);

        $objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $class
        )->will(
            $this->returnValue($imageAdapterMock)
        );

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $adapterFactory->create($alias);
    }
}
