<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class InitialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

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
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $serializerMock->method('unserialize')
            ->willReturn($this->data);

        $this->objectManager->mockObjectManager(
            [\Magento\Framework\Serialize\SerializerInterface::class => $serializerMock]
        );

        $this->config = new \Magento\Framework\App\Config\Initial(
            $this->readerMock,
            $this->cacheMock
        );
    }

    protected function tearDown()
    {
        $this->objectManager->restoreObjectManager();
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
}
