<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Frontend;

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Test\Unit\Cache\Frontend\FactoryTest\CacheDecoratorDummy;
use Magento\Framework\Cache\Core;
use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/FactoryTest/CacheDecoratorDummy.php';
    }

    public function testCreate()
    {
        $model = $this->_buildModelForCreate();
        $result = $model->create(['backend' => 'Zend_Cache_Backend_BlackHole']);

        $this->assertInstanceOf(
            FrontendInterface::class,
            $result,
            'Created object must implement \Magento\Framework\Cache\FrontendInterface'
        );
        $this->assertInstanceOf(
            Core::class,
            $result->getLowLevelFrontend(),
            'Created object must have \Magento\Framework\Cache\Core frontend by default'
        );
        $this->assertInstanceOf(
            'Zend_Cache_Backend_BlackHole',
            $result->getBackend(),
            'Created object must have backend as configured in backend options'
        );
    }

    public function testCreateOptions()
    {
        $model = $this->_buildModelForCreate();
        $result = $model->create(
            [
                'backend' => 'Zend_Cache_Backend_Static',
                'frontend_options' => ['lifetime' => 2601],
                'backend_options' => ['file_extension' => '.wtf'],
            ]
        );

        $frontend = $result->getLowLevelFrontend();
        $backend = $result->getBackend();

        $this->assertEquals(2601, $frontend->getOption('lifetime'));
        $this->assertEquals('.wtf', $backend->getOption('file_extension'));
    }

    public function testCreateEnforcedOptions()
    {
        $model = $this->_buildModelForCreate(['backend' => 'Zend_Cache_Backend_Static']);
        $result = $model->create(['backend' => 'Zend_Cache_Backend_BlackHole']);

        $this->assertInstanceOf('Zend_Cache_Backend_Static', $result->getBackend());
    }

    /**
     * @param array $options
     * @param string $expectedPrefix
     * @dataProvider idPrefixDataProvider
     */
    public function testIdPrefix($options, $expectedPrefix)
    {
        $model = $this->_buildModelForCreate(['backend' => 'Zend_Cache_Backend_Static']);
        $result = $model->create($options);

        $frontend = $result->getLowLevelFrontend();
        $this->assertEquals($expectedPrefix, $frontend->getOption('cache_id_prefix'));
    }

    /**
     * @return array
     */
    public static function idPrefixDataProvider()
    {
        return [
            // start of md5('DIR')
            'default id prefix' => [['backend' => 'Zend_Cache_Backend_BlackHole'], 'c15_'],
            'id prefix in "id_prefix" option' => [
                ['backend' => 'Zend_Cache_Backend_BlackHole', 'id_prefix' => 'id_prefix_value'],
                'id_prefix_value',
            ],
            'id prefix in "prefix" option' => [
                ['backend' => 'Zend_Cache_Backend_BlackHole', 'prefix' => 'prefix_value'],
                'prefix_value',
            ]
        ];
    }

    public function testCreateDecorators()
    {
        $model = $this->_buildModelForCreate(
            [],
            [
                [
                    'class' => CacheDecoratorDummy::class,
                    'parameters' => ['param' => 'value'],
                ]
            ]
        );
        $result = $model->create(['backend' => 'Zend_Cache_Backend_BlackHole']);

        $this->assertInstanceOf(
            CacheDecoratorDummy::class,
            $result
        );

        $params = $result->getParams();
        $this->assertArrayHasKey('param', $params);
        $this->assertEquals($params['param'], 'value');
    }

    /**
     * Create the model to be tested, providing it with all required dependencies
     *
     * @param array $enforcedOptions
     * @param array $decorators
     * @return Factory
     * phpcs:disable Squiz.PHP.NonExecutableCode.Unreachable
     */
    protected function _buildModelForCreate($enforcedOptions = [], $decorators = [])
    {
        $processFrontendFunc = function ($class, $params) {
            switch ($class) {
                case Zend::class:
                    return new $class($params['frontendFactory']);
                case CacheDecoratorDummy::class:
                    $frontend = $params['frontend'];
                    unset($params['frontend']);
                    return new $class($frontend, $params);
                default:
                    throw new \Exception("Test is not designed to create {$class} objects");
                    break;
            }
        };
        /** @var MockObject $objectManager */
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager->expects($this->any())->method('create')->willReturnCallback($processFrontendFunc);

        $dirMock = $this->getMockForAbstractClass(ReadInterface::class);
        $dirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('DIR');
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($dirMock);
        $filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($dirMock);

        $resource = $this->createMock(ResourceConnection::class);

        $model = new Factory(
            $objectManager,
            $filesystem,
            $resource,
            $enforcedOptions,
            $decorators
        );

        return $model;
    }
}
