<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\ObjectManager\Test
 */
namespace Magento\Test;

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

        $configMock = $this->getMockBuilder('Magento\TestFramework\ObjectManager\Config')
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

        $cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', [], [], '', false);
        $configCache = $this->getMock('Magento\Framework\App\ObjectManager\ConfigCache', [], [], '', false);
        $primaryLoaderMock = $this->getMock(
            'Magento\Framework\App\ObjectManager\ConfigLoader\Primary',
            [],
            [],
            '',
            false
        );
        $factory = $this->getMock('Magento\Framework\ObjectManager\FactoryInterface');
        $factory->expects($this->exactly(2))->method('create')->will(
            $this->returnCallback(
                function ($className) {
                    if ($className === 'Magento\Framework\DataObject') {
                        return $this->getMock('Magento\Framework\DataObject', [], [], '', false);
                    }
                }
            )
        );

        $connectionMock = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $sharedInstances = [
            'Magento\Framework\App\Cache\Type\Config' => $cache,
            'Magento\Framework\App\ObjectManager\ConfigLoader' => $configLoader,
            'Magento\Framework\App\ObjectManager\ConfigCache' => $configCache,
            'Magento\Framework\Config\ReaderInterface' => $this->getMock(
                'Magento\Framework\Config\ReaderInterface'
            ),
            'Magento\Framework\Config\ScopeInterface' => $this->getMock('Magento\Framework\Config\ScopeInterface'),
            'Magento\Framework\Config\CacheInterface' => $this->getMock('Magento\Framework\Config\CacheInterface'),
            'Magento\Framework\Cache\FrontendInterface' =>
                $this->getMock('Magento\Framework\Cache\FrontendInterface'),
            'Magento\Framework\App\ResourceConnection' => $connectionMock,
            'Magento\Framework\App\ResourceConnection\Config' => $this->getMock(
                'Magento\Framework\App\ResourceConnection\Config',
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

        $model->addSharedInstance($resource, 'Magento\Framework\App\ResourceConnection');
        $instance1 = $model->get('Magento\Framework\DataObject');

        $this->assertSame($instance1, $model->get('Magento\Framework\DataObject'));
        $this->assertSame($model, $model->clearCache());
        $this->assertSame($model, $model->get('Magento\Framework\ObjectManagerInterface'));
        $this->assertSame($resource, $model->get('Magento\Framework\App\ResourceConnection'));
        $this->assertNotSame($instance1, $model->get('Magento\Framework\DataObject'));
    }
}
