<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\Json\JsonInterface;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    protected $object;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\DomFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerFactoryMock;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            \Magento\Framework\ObjectManager\Config\Reader\Dom::class,
            [],
            [],
            '',
            false
        );

        $this->readerFactoryMock = $this->getMock(
            \Magento\Framework\ObjectManager\Config\Reader\DomFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->readerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->readerMock)
        );

        $this->cacheMock = $this->getMock(\Magento\Framework\App\Cache\Type\Config::class, [], [], '', false);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->object = $objectManagerHelper->getObject(
            \Magento\Framework\App\ObjectManager\ConfigLoader::class,
            [
                'cache' => $this->cacheMock,
                'readerFactory' => $this->readerFactoryMock,
            ]
        );
        $jsonMock = $this->getMock(JsonInterface::class, [], [], '', false);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_encode($string);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'json',
            $jsonMock
        );
        $jsonMock = $this->getMock(JsonInterface::class, [], [], '', false);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_encode($string);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'json',
            $jsonMock
        );
        $jsonMock = $this->getMock(JsonInterface::class, [], [], '', false);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_encode($string);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'json',
            $jsonMock
        );
        $jsonMock = $this->getMock(JsonInterface::class, [], [], '', false);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_encode($string);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'json',
            $jsonMock
        );
    }

    /**
     * @param $area
     * @dataProvider loadDataProvider
     */
    public function testLoad($area)
    {
        $configData = ['some' => 'config', 'data' => 'value'];

        $this->cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $area . '::DiConfig'
        )->will(
            $this->returnValue(false)
        );

        $this->readerMock->expects($this->once())->method('read')->with($area)->will($this->returnValue($configData));

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
}
