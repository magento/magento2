<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Image;

class AdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Image\Adapter\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    public function setUp()
    {
        $this->configMock = $this->getMock(
            'Magento\Framework\Image\Adapter\ConfigInterface',
            array('getAdapterAlias', 'getAdapters'),
            array(),
            '',
            false
        );

        $this->configMock->expects(
            $this->once()
        )->method(
            'getAdapters'
        )->will(
            $this->returnValue(
                array(
                    'GD2' => array('class' => 'Magento\Framework\Image\Adapter\Gd2'),
                    'IMAGEMAGICK' => array('class' => 'Magento\Framework\Image\Adapter\ImageMagick'),
                    'wrongInstance' => array('class' => 'stdClass'),
                    'test' => array()
                )
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
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            array('create'),
            array(),
            '',
            false
        );
        $imageAdapterMock = $this->getMock($class, array('checkDependencies'), array(), '', false);
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
        return array(
            array('GD2', 'Magento\Framework\Image\Adapter\Gd2'),
            array('IMAGEMAGICK', 'Magento\Framework\Image\Adapter\ImageMagick')
        );
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     */
    public function testCreateWithoutName()
    {
        $adapterAlias = 'IMAGEMAGICK';
        $adapterClass = 'Magento\Framework\Image\Adapter\ImageMagick';

        $this->configMock->expects($this->once())->method('getAdapterAlias')->will($this->returnValue($adapterAlias));

        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            array('create'),
            array(),
            '',
            false
        );
        $imageAdapterMock = $this->getMock($adapterClass, array('checkDependencies'), array(), '', false);
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
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            array('create'),
            array(),
            '',
            false
        );
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
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            array('create'),
            array(),
            '',
            false
        );

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
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            array('create'),
            array(),
            '',
            false
        );
        $imageAdapterMock = $this->getMock($class, array('checkDependencies'));

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
