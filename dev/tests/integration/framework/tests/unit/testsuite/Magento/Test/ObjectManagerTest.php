<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\ObjectManager\Test
 */
namespace Magento\Test;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expected instance manager parametrized cache after clear
     *
     * @var array
     */
    protected $_instanceCache = ['hashShort' => [], 'hashLong' => []];

    public function testClearCache()
    {
        $resource = new \stdClass();

        $configMock = $this->getMockBuilder(\Magento\TestFramework\ObjectManager\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPreference', 'clean'])
            ->getMock();

        $configMock->expects($this->atLeastOnce())
            ->method('getPreference')
            ->will($this->returnCallback(
                function ($className) {
                    return $className;
                }
            ));

        $cache = $this->getMock(\Magento\Framework\App\CacheInterface::class);
        $configLoader = $this->getMock(\Magento\Framework\App\ObjectManager\ConfigLoader::class, [], [], '', false);
        $configCache = $this->getMock(\Magento\Framework\App\ObjectManager\ConfigCache::class, [], [], '', false);
        $primaryLoaderMock = $this->getMock(
            \Magento\Framework\App\ObjectManager\ConfigLoader\Primary::class,
            [],
            [],
            '',
            false
        );
        $factory = $this->getMock(\Magento\Framework\ObjectManager\FactoryInterface::class);
        $factory->expects($this->exactly(2))->method('create')->will(
            $this->returnCallback(
                function ($className) {
                    if ($className === \Magento\Framework\DataObject::class) {
                        return $this->getMock(\Magento\Framework\DataObject::class, [], [], '', false);
                    }
                }
            )
        );

        $connectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedInstances = [
            \Magento\Framework\App\Cache\Type\Config::class => $cache,
            \Magento\Framework\App\ObjectManager\ConfigLoader::class => $configLoader,
            \Magento\Framework\App\ObjectManager\ConfigCache::class => $configCache,
            \Magento\Framework\Config\ReaderInterface::class =>
                $this->getMock(
                    \Magento\Framework\Config\ReaderInterface::class
                ),
            \Magento\Framework\Config\ScopeInterface::class =>
                $this->getMock(\Magento\Framework\Config\ScopeInterface::class),
            \Magento\Framework\Config\CacheInterface::class =>
                $this->getMock(\Magento\Framework\Config\CacheInterface::class),
            \Magento\Framework\Cache\FrontendInterface::class =>
                $this->getMock(\Magento\Framework\Cache\FrontendInterface::class),
            \Magento\Framework\App\ResourceConnection::class => $connectionMock,
            \Magento\Framework\App\ResourceConnection\Config::class =>
                $this->getMock(
                    \Magento\Framework\App\ResourceConnection\Config::class,
                    [],
                    [],
                    '',
                    false
                )
        ];
        $model = new \Magento\TestFramework\ObjectManager(
            $factory,
            $configMock,
            $sharedInstances,
            $primaryLoaderMock
        );

        $model->addSharedInstance($resource, \Magento\Framework\App\ResourceConnection::class);
        $instance1 = $model->get(\Magento\Framework\DataObject::class);

        $this->assertSame($instance1, $model->get(\Magento\Framework\DataObject::class));
        $this->assertSame($model, $model->clearCache());
        $this->assertSame($model, $model->get(\Magento\Framework\ObjectManagerInterface::class));
        $this->assertSame($resource, $model->get(\Magento\Framework\App\ResourceConnection::class));
        $this->assertNotSame($instance1, $model->get(\Magento\Framework\DataObject::class));
    }
}
