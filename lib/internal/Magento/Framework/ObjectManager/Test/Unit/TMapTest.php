<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/TMap/TClass.php';
require_once __DIR__ . '/_files/TMap/TInterface.php';

class TMapTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $om;

    /**
     * @var MockObject|ConfigInterface
     */
    private $omConfig;

    protected function setUp(): void
    {
        $this->om = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->omConfig = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
    }

    public function testConstructor()
    {
        $tMap = $this->getSimpleInitialized(3);
        static::assertEquals(3, $tMap->count());
    }

    public function testRead()
    {
        $tMap = $this->getSimpleInitialized(3);
        $this->om->expects(static::exactly(3))
            ->method('create')
            ->willReturnMap(
                [
                    ['TClass', [], new \TClass()],
                    ['TInterface', [], new \TClass()],
                    ['TClassVirtual', [], new \TClass()]
                ]
            );

        static::assertContainsOnlyInstancesOf('\TInterface', $tMap);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testRemove()
    {
        $tMap = $this->getSimpleInitialized(3);

        static::assertEquals(3, $tMap->count());
        foreach ($tMap as $key => $instance) {
            unset($tMap[$key]);
        }
        static::assertEquals(0, $tMap->count());
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testEdit()
    {
        $expectedKeysOrder = [
            'item',
            4,
            'item2',
            5
        ];
        $tMap = $this->getSimpleInitialized(6);

        unset($tMap[0], $tMap[3]);

        $tMap[] = 'TClassVirtual';
        $tMap['item2'] = 'TClass';
        $tMap[] = 'TInterface';

        $this->om->expects(static::exactly(4))
            ->method('create')
            ->willReturnMap(
                [
                    ['TClass', [], new \TClass()],
                    ['TInterface', [], new \TClass()],
                    ['TClassVirtual', [], new \TClass()]
                ]
            );

        $i = 0;
        foreach ($tMap as $key => $item) {
            static::assertEquals($expectedKeysOrder[$i], $key);
            $i++;
        }

        static::assertEquals(4, $tMap->count());
    }

    /**
     * Returns simple initialized tMap
     *
     * @param int $exactlyCalls
     * @return TMap
     */
    private function getSimpleInitialized($exactlyCalls = 3)
    {
        /**
            [
                0 => ['TClass', 'TClass', 'TClass'],
                'item' => ['TClassVirtual', 'TClassVirtual', 'TClass'],
                3 => ['TInterface', 'TClassVirtual', 'TClass']
            ];
        */
        $testClasses = [
            0 => 'TClass',
            'item' => 'TClassVirtual',
            3 => 'TInterface'
        ];

        $this->omConfig->expects(static::exactly($exactlyCalls))
            ->method('getPreference')
            ->willReturnMap(
                [
                    ['TClass', 'TClass'],
                    ['TClassVirtual', 'TClassVirtual'],
                    ['TInterface', 'TClassVirtual']
                ]
            );
        $this->omConfig->expects(static::exactly($exactlyCalls))
            ->method('getInstanceType')
            ->willReturnMap(
                [
                    ['TClass', 'TClass'],
                    ['TClassVirtual', 'TClass']
                ]
            );

        return new TMap(
            'TInterface',
            $this->om,
            $this->omConfig,
            $testClasses,
            function (ObjectManagerInterface $om, $objectName) {
                return $om->create($objectName);
            }
        );
    }
}
