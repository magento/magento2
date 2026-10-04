<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\Lock\Backend\Database;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;

class LockGuardedCacheLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $om;

    /**
     * @var LockGuardedCacheLoader|null
     */
    private ?LockGuardedCacheLoader $lockGuardedCacheLoader;

    protected function setUp(): void
    {
        $this->om = Bootstrap::getObjectManager();
        $this->lockGuardedCacheLoader = $this->om
            ->create(
                LockGuardedCacheLoader::class,
                [
                    'locker' => $this->om->get(Database::class)
                ]
            );
    }

    /**
     * @param $lockName
     * @param $dataLoader
     * @param $dataCollector
     * @param $dataSaver
     * @param $expected
     * @return void
     */
    #[DataProvider('dataProviderLockGuardedCacheLoader')]
    public function testLockedLoadData(
        $lockName,
        $dataLoader,
        $dataCollector,
        $dataSaver,
        $expected
    ) {
        $result = $this->lockGuardedCacheLoader->lockedLoadData(
            $lockName,
            $dataLoader,
            $dataCollector,
            $dataSaver
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public static function dataProviderLockGuardedCacheLoader(): array
    {
        return [
            'Data loader read' => [
                'lockName',
                function () {
                    return ['data1', 'data2'];
                },
                function () {
                    return ['data3', 'data4'];
                },
                function () {
                    return new \stdClass();
                },
                ['data1', 'data2'],
            ],
            'Data collector read' => [
                'lockName',
                function () {
                    return false;
                },
                function () {
                    return ['data3', 'data4'];
                },
                function () {
                    return new \stdClass();
                },
                ['data3', 'data4'],
            ],
        ];
    }
}
