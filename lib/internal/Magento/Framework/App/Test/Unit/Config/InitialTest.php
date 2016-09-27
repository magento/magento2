<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class InitialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\Initial\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var array
     */
    private $data = [
        'data' => [
            'default' => ['key' => 'default_value'],
            'stores' => ['default' => ['key' => 'store_value']],
            'websites' => ['default' => ['key' => 'website_value']],
        ],
        'metadata' => ['metadata'],
    ];

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            \Magento\Framework\App\Config\Initial\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMock(
            \Magento\Framework\App\Cache\Type\Config::class,
            [],
            [],
            '',
            false
        );
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->with('initial_config')
            ->willReturn(json_encode($this->data));
        $jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        $jsonMock->method('decode')
            ->willReturn($this->data);

        $this->prepareObjectManager([
            [\Magento\Framework\Json\JsonInterface::class, $jsonMock]
        ]);

        $this->config = new \Magento\Framework\App\Config\Initial(
            $this->readerMock,
            $this->cacheMock
        );
    }

    /**
     * @param string $scope
     * @param array $expected
     * @dataProvider getDataDataProvider
     */
    public function testGetData($scope, $expected)
    {
        $this->assertEquals($expected, $this->config->getData($scope));
    }

    public function getDataDataProvider()
    {
        return [
            ['default', ['key' => 'default_value']],
            ['stores|default', ['key' => 'store_value']],
            ['websites|default', ['key' => 'website_value']]
        ];
    }

    public function testGetMetadata()
    {
        $this->assertEquals(['metadata'], $this->config->getMetadata());
    }

    /**
     * @param array $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}
