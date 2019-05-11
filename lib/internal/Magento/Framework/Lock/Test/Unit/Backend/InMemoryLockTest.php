<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Lock\Backend\InMemoryLock;
use PHPUnit\Framework\TestCase;

/**
 * Test for simple in memory backend for lock manager
 */
class InMemoryLockTest extends TestCase
{
    /** @var InMemoryLock */
    private $lockManager;

    protected function setUp()
    {
        $this->lockManager = new InMemoryLock();
    }

    /** @test */
    public function reportsNothingIsLockedByDefault()
    {
        $this->assertFalse(
            $this->lockManager->isLocked('lock1')
        );
    }
    
    /** @test */
    public function reportsLockedRecord()
    {
        $this->lockManager->lock('lock1');

        $this->assertTrue(
            $this->lockManager->isLocked('lock1')
        );
    }

    /** @test */
    public function reportsUnLockedRecordNameDoesNotMatch()
    {
        $this->lockManager->lock('lock2');

        $this->assertFalse(
            $this->lockManager->isLocked('lock1')
        );
    }
    
    /** @test */
    public function reportsSuccessfullyAcquiredLock()
    {
        $this->assertTrue(
            $this->lockManager->lock('lock2')
        );
    }
    
    /** @test */
    public function doesNotLetYouAcquireLockWhenRecordIsLockedAlready()
    {
        $this->lockManager->lock('lock3');

        $this->assertFalse(
            $this->lockManager->lock('lock3')
        );
    }
    
    /** @test */
    public function releasesLock()
    {
        $this->lockManager->lock('lock2');

        $this->lockManager->unlock('lock2');

        $this->assertFalse(
            $this->lockManager->isLocked('lock2')
        );
    }
    
    /** @test */
    public function preservesOtherLockedRecords()
    {
        $this->lockManager->lock('lock1');
        $this->lockManager->lock('lock2');

        $this->lockManager->unlock('lock1');

        $this->assertTrue(
            $this->lockManager->isLocked('lock2')
        );
    }
    
    /** @test */
    public function reportsSuccessfullyRemovedLock()
    {
        $this->lockManager->lock('lock3');

        $this->assertTrue(
            $this->lockManager->unlock('lock3')
        );
    }
    
    /** @test */
    public function reportsThatLockWasNotUnlockedWhenNoLockWasAvailable()
    {
        $this->lockManager->lock('lock3');

        $this->lockManager->unlock('lock3');

        $this->assertFalse(
            $this->lockManager->unlock('lock3')
        );
    }

}
