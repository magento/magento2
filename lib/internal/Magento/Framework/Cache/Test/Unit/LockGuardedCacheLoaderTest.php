<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Lock\Backend\InMemoryLock;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Cache\LockGuardedCacheLoader test case
 */
class LockGuardedCacheLoaderTest extends TestCase
{
    /** @var InMemoryLock */
    private $lockManager;

    /** @var LockGuardedCacheLoader */
    private $lockGuard;

    /** @var callable[] */
    private $loadSequence = [];

    /** @var mixed */
    private $cachedData;

    protected function setUp()
    {
        $this->lockManager = new InMemoryLock();
        $this->lockGuard = new LockGuardedCacheLoader(
            $this->lockManager,
            1000,
            5
        );
    }

    /** @test */
    public function blockingLoaderReturnsUncachedDataWhenNothingIsRetrievedFromCaches()
    {
        $result = $this->lockGuard->lockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            $this->doNothing()
        );

        $this->assertEquals(
            ['uncached'],
            $result
        );
    }
    
    /** @test */
    public function blockingLoaderWaitsForCacheBeingUnlockedBeforeLoadingData()
    {
        $this->addLoadSequence(function () {
            $this->lockManager->lock('lock1');
            return false;
        });

        $this->addLoadSequence(function () {
            $this->lockManager->unlock('lock1');
            return ['cached'];
        });

        $result = $this->lockGuard->lockedLoadData(
            'lock1',
            $this->loadFromStorage(),
            $this->readValue(['uncached']),
            $this->doNothing()
        );

        $this->assertEquals(['cached'], $result);
    }


    /** @test */
    public function blockingLoaderWaitsForLockReleaseBeforeLoadingUnCachedData()
    {
        $this->addLoadSequence(function () {
            $this->lockManager->lock('lock1');
            return false;
        });

        $this->addLoadSequence(function () {
            $this->lockManager->unlock('lock1');
            return false;
        });

        $result = $this->lockGuard->lockedLoadData(
            'lock1',
            $this->loadFromStorage(),
            $this->readValue(['uncached']),
            $this->doNothing()
        );

        $this->assertEquals(['uncached'], $result);
    }

    /** @test */
    public function blockingLoaderStoresDataViaSaveHandle()
    {
        $this->addLoadSequence(function () {
            $this->lockManager->lock('lock1');
            return false;
        });

        $this->addLoadSequence(function () {
            $this->lockManager->unlock('lock1');
            return false;
        });

        $this->lockGuard->lockedLoadData(
            'lock1',
            $this->loadFromStorage(),
            $this->readValue(['uncached_saved']),
            function ($data) {
                $this->cachedData = $data;
            }
        );

        $this->assertEquals(['uncached_saved'], $this->cachedData);
    }

    /** @test */
    public function nonBlockingLoaderReturnsUncachedDataWhenNothingIsRetrievedFromCaches()
    {
        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            function ($data) {
                return $data;
            }
        );

        $this->assertEquals(
            ['uncached'],
            $result
        );
    }

    /** @test */
    public function nonBlockingLoaderReturnsCachedDataEvenIfItIsLocked()
    {
        $this->lockManager->lock('lock1');

        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return ['cached'];
            },
            function () {
                return ['uncached'];
            },
            $this->doNothing()
        );

        $this->assertEquals(
            ['cached'],
            $result
        );
    }

    /** @test */
    public function nonBlockingLoaderReturnsUnCachedDataWhenItIsLocked()
    {
        $this->lockManager->lock('lock1');

        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            function ($data) {
                return [current($data), 'cached'];
            }
        );

        $this->assertEquals(
            ['uncached'],
            $result
        );
    }

    /** @test */
    public function nonBlockingLoaderStoresDataAndPassesThroughResultFromSaveHandle()
    {
        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            function ($data) {
                return [current($data), 'cached'];
            }
        );

        $this->assertEquals(
            ['uncached', 'cached'],
            $result
        );
    }

    /** @test */
    public function nonBlockingLoaderReleasesLockAfterCacheHasBeenSaved()
    {
        $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            function ($data) {
                return [current($data), 'cached'];
            }
        );

        $this->assertFalse($this->lockManager->isLocked('lock1'));
    }

    /** @test */
    public function nonBlockingLoaderReleasesLockWhenErrorHappens()
    {
        try {
            $this->lockGuard->nonBlockingLockedLoadData(
                'lock1',
                function () {
                    return false;
                },
                function () {
                    return ['uncached'];
                },
                function () {
                    throw new \Exception('Something went wrong');
                }
            );
        } catch (\Exception $exception) {

        }

        $this->assertFalse($this->lockManager->isLocked('lock1'));
    }


    private function addLoadSequence(callable $loadOperation): void
    {
        $this->loadSequence[] = $loadOperation;
    }

    private function loadFromStorage(): callable
    {
        return function () {
            return array_shift($this->loadSequence)();
        };
    }

    private function readValue($value): callable
    {
        return function () use ($value) {
            return $value;
        };
    }

    private function doNothing(): callable
    {
        return function () {
        };
    }

}
