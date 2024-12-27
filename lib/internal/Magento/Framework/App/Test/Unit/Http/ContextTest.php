<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Http;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context
     */
    protected $object;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->objectManager = new ObjectManager($this);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->onlyMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->object = $this->objectManager->getObject(
            Context::class,
            [
                'serializer' => $this->serializerMock
            ]
        );
        $this->deploymentConfig = $this->createPartialMock(
            DeploymentConfig::class,
            ['get']
        );
    }

    public function testGetValue()
    {
        $this->assertNull($this->object->getValue('key'));
    }

    public function testSetGetValue()
    {
        $this->object->setValue('key', 'value', 'default');
        $this->assertEquals('value', $this->object->getValue('key'));
    }

    public function testSetUnsetGetValue()
    {
        $this->object->setValue('key', 'value', 'default');
        $this->object->unsValue('key');
        $this->assertEquals('default', $this->object->getValue('key'));
    }

    public function testGetData()
    {
        $this->object->setValue('key1', 'value1', 'default1');
        $this->object->setValue('key2', 'value2', 'default2');
        $this->object->setValue('key3', 'value3', 'value3');
        $this->object->unsValue('key1');
        $this->assertEquals(['key2' => 'value2'], $this->object->getData());
    }

    public function testGetVaryString()
    {
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(DeploymentConfig::class)
            ->willReturn($this->deploymentConfig);

        $this->deploymentConfig->expects($this->any())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY)
            ->willReturn('448198e08af35844a42d3c93c1ef4e03');

        $this->object->setValue('key2', 'value2', 'default2');
        $this->object->setValue('key1', 'value1', 'default1');
        $data = [
            'key2' => 'value2',
            'key1' => 'value1'
        ];
        ksort($data);

        $salt = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $cacheKey = hash('sha256', $this->serializerMock->serialize($data) . '|' . $salt);

        $this->assertEquals($cacheKey, $this->object->getVaryString());
    }

    public function testToArray()
    {
        $newObject = new Context(['key' => 'value'], [], $this->serializerMock);

        $newObject->setValue('key1', 'value1', 'default1');
        $newObject->setValue('key2', 'value2', 'default2');
        $this->assertEquals(
            [
                'data' => ['key' => 'value', 'key1' => 'value1', 'key2' => 'value2'],
                'default' => ['key1' => 'default1', 'key2' => 'default2']
            ],
            $newObject->toArray()
        );
    }
}
