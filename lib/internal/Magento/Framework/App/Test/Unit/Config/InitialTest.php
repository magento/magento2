<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitialTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Initial
     */
    private $config;

    /**
     * @var Config|MockObject
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

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->cacheMock = $this->createMock(Config::class);
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->with('initial_config')
            ->willReturn(json_encode($this->data));
        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('unserialize')
            ->willReturn($this->data);

        $this->config = $this->objectManager->getObject(
            Initial::class,
            [
                'cache' => $this->cacheMock,
                'serializer' => $serializerMock,
            ]
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

    /**
     * @return array
     */
    public static function getDataDataProvider()
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
