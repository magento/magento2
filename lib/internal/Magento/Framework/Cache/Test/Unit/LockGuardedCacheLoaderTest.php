<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Cache\StaleCacheNotifierInterface;
use Magento\Framework\Lock\Backend\InMemoryLock;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Cache\LockGuardedCacheLoader test case
 */
class LockGuardedCacheLoaderTest extends TestCase implements StaleCacheNotifierInterface
{
    /** @var InMemoryLock */
    private $lockManager;

    /** @var LockGuardedCacheLoader */
    private $lockGuard;

    /** @var callable[] */
    private $loadSequence = [];

    /** @var mixed */
    private $cachedData;

    /** @var string[] */
    private $notifications = [];

    protected function setUp()
    {
        $this->lockManager = new InMemoryLock();
        $this->lockGuard = new LockGuardedCacheLoader(
            $this->lockManager,
            1000,
            5,
            $this
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
        $this->addLoadSequence(
            function () {
                $this->lockManager->lock('lock1');
                return false;
            }
        );

        $this->addLoadSequence(
            function () {
                $this->lockManager->unlock('lock1');
                return ['cached'];
            }
        );

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
        $this->addLoadSequence(
            function () {
                $this->lockManager->lock('lock1');
                return false;
            }
        );

        $this->addLoadSequence(
            function () {
                $this->lockManager->unlock('lock1');
                return false;
            }
        );

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
        $this->addLoadSequence(
            function () {
                $this->lockManager->lock('lock1');
                return false;
            }
        );

        $this->addLoadSequence(
            function () {
                $this->lockManager->unlock('lock1');
                return false;
            }
        );

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
            $this->returnPassedDataWithMergedValue([])
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
            $this->returnPassedDataWithMergedValue(['cached'])
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
            $this->returnPassedDataWithMergedValue(['cached'])
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
            $this->returnPassedDataWithMergedValue(['cached'])
        );

        $this->assertFalse($this->lockManager->isLocked('lock1'));
    }

    /** @test */
    public function nonBlockingLoaderDoesNotTryStoreCachedDataWhenDataWasLocked()
    {
        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                $this->lockManager->lock('lock1');
                return false;
            },
            function () {
                $this->lockManager->unlock('lock1');
                return ['uncached'];
            },
            $this->returnPassedDataWithMergedValue(['cached'])
        );

        $this->assertEquals(['uncached'], $result);
    }

    /** @test */
    public function nonBlockingLoaderPreventsSecondWriteOperationIfCacheWasAlreadyLockedBefore()
    {
        $this->lockManager->lock('lock1');

        $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            $this->returnPassedDataWithMergedValue(['cached'])
        );

        $this->lockManager->unlock('lock1');

        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () {
                return false;
            },
            function () {
                return ['uncached'];
            },
            $this->returnPassedDataWithMergedValue(['cached'])
        );

        $this->assertEquals(['uncached'], $result);
    }

    /** @test */
    public function nonBlockingLoaderTriesLoadingCacheAtLeastTwoTimesWhenItIsLocked()
    {
        $lockSequence = [
            function () {
                $this->lockManager->lock('lock1');
                return false;
            },
            function () {
                return ['cached'];
            }
        ];

        $result = $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            function () use (&$lockSequence) {
                if ($lockSequence) {
                    return array_shift($lockSequence)();
                }
                return false;
            },
            function () {
                return ['uncached'];
            },
            $this->returnPassedDataWithMergedValue(['cache_saved'])
        );

        $this->assertEquals(['cached'], $result);
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
                $this->throwError()
            );
        } catch (\Exception $exception) {
            // Ignore error
        }

        $this->assertFalse($this->lockManager->isLocked('lock1'));
    }

    /** @test */
    public function nonBlockingLoaderDoesNotNotifyOfStaleCacheOnRegularCacheLoad()
    {
        $this->lockGuard->nonBlockingLockedLoadData(
            'lock1',
            $this->readValue(['cached']),
            $this->doNothing(),
            $this->doNothing()
        );

        $this->assertEmpty($this->notifications);
    }

    /** @test */
    public function nonBlockingLoaderNotifiesOfStaleCacheOnFailedToAcquireLock()
    {
        $this->lockManager->lock('lock2');

        $this->lockGuard->nonBlockingLockedLoadData(
            'lock2',
            $this->readValue(false),
            $this->doNothing(),
            $this->doNothing()
        );

        $this->assertEquals(['staleCacheLoaded'], $this->notifications);
    }

    /** @test */
    public function nonBlockingLoaderDoesNotNotifyOfStaleCacheAcquiredLock()
    {
        $this->lockGuard->nonBlockingLockedLoadData(
            'lock2',
            $this->readValue(false),
            $this->doNothing(),
            $this->doNothing()
        );

        $this->assertEmpty($this->notifications);
    }

    /** @test */
    public function dataFormatterGetsInvokedOnNonSavedCacheFlow()
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
            $this->returnPassedDataWithMergedValue(['saved']),
            $this->returnPassedDataWithMergedValue(['formatted'])
        );

        $this->assertEquals(['uncached', 'formatted'], $result);
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

    private function throwError(): callable
    {
        return function () {
            throw new \Exception('Something went wrong');
        };
    }

    private function returnPassedDataWithMergedValue($valueToMerge): callable
    {
        return function ($data) use ($valueToMerge) {
            return array_merge($data, $valueToMerge);
        };
    }

    /**
     * Self-shunting notifier to test behavior of composite
     */
    public function cacheLoaderIsUsingStaleCache(): void
    {
        $this->notifications[] = 'staleCacheLoaded';
    }
}
