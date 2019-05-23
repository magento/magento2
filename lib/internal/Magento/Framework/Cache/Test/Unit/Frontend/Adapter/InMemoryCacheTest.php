<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\Cache\Frontend\Adapter\InMemoryCache;
use PHPUnit\Framework\TestCase;

class InMemoryCacheTest extends TestCase
{
    /** @var InMemoryCache */
    private $frontend;

    protected function setUp()
    {
        $this->frontend = new InMemoryCache();
    }

    /** @test */
    public function testingForCacheAvailabilityResultsInFalseWhenStorageIsEmpty()
    {
        $this->assertEquals(false, $this->frontend->test('cache_key_one'));
    }
    
    /** @test */
    public function whenCacheIsSavedTestingForItsAvailabilityReturnsTrue()
    {
        $this->frontend->save('data', 'cache_key_one');

        $this->assertEquals(true, $this->frontend->test('cache_key_one'));
    }
    
    /** @test */
    public function whenUnrelatedCacheIsSavedOnlyRequestedCacheKeyTestValidationReturnsResult()
    {
        $this->frontend->save('data', 'cache_key_two');

        $this->assertEquals(false, $this->frontend->test('cache_key_one'));
    }
    
    /** @test */
    public function savedDataCanBeRetrievedByLoadingWithCacheKey()
    {
        $this->frontend->save('data_one', 'cache_key_one');

        $this->assertEquals('data_one', $this->frontend->load('cache_key_one'));
    }

    /** @test */
    public function whenDataWasNotSavedLoadingItReturnsFalse()
    {
        $this->assertEquals(false, $this->frontend->load('cache_key_two'));
    }
    
    /** @test */
    public function removedCacheEntryResultsInFalseDuringLoad()
    {
        $this->frontend->save('data_one', 'cache_key_one');
        $this->frontend->remove('cache_key_one');

        $this->assertEquals(false, $this->frontend->load('cache_key_one'));
    }
    
    /** @test */
    public function clearsAllCache()
    {
        $this->frontend->save('data_one', 'cache_key_one');
        $this->frontend->save('data_two', 'cache_key_two');
        $this->frontend->clean();

        $this->assertEquals(
            [
                false,
                false
            ],
            [
                $this->frontend->load('cache_key_one'),
                $this->frontend->load('cache_key_two')
            ],
            'Clearing cache has failed, data is returned as saved'
        );
    }
    
    /** @test */
    public function clearCacheByTagOnlyTaggedRecordsAreRemoved()
    {
        $this->frontend->save('data_with_tag_one', 'cache_key_one', ['tag_one']);
        $this->frontend->save('data_two', 'cache_key_two');

        $this->frontend->clean($this->frontend::CLEAN_MATCHING_TAG, ['tag_one']);

        $this->assertEquals(
            [
                false,
                'data_two'
            ],
            [
                $this->frontend->load('cache_key_one'),
                $this->frontend->load('cache_key_two')
            ],
            'Clearing cache by tag removed un-related records'
        );
    }
    
    /** @test */
    public function clearingByTagsRemovesRecordsWithMultipleTagsMatchingOnesInRequest()
    {
        $this->frontend->save('data_one', 'cache_key_one', ['tag_one', 'unrelated_tag', 'tag_four']);
        $this->frontend->save('data_two', 'cache_key_two', ['unrelated_tag']);
        $this->frontend->save('data_three', 'cache_key_three', ['tag_four']);
        $this->frontend->save('data_four', 'cache_key_four', ['tag_four', 'tag_one']);

        $this->frontend->clean($this->frontend::CLEAN_MATCHING_TAG, ['tag_one', 'tag_four']);

        $this->assertEquals(
            [
                false,
                'data_two',
                'data_three',
                false
            ],
            [
                $this->frontend->load('cache_key_one'),
                $this->frontend->load('cache_key_two'),
                $this->frontend->load('cache_key_three'),
                $this->frontend->load('cache_key_four'),
            ],
            'Clearing cache by tag removed un-related records'
        );
    }

    /** @test */
    public function clearingCacheByTagsWithEmptyTagsRequestDoesNotClearAnyRecords()
    {
        $this->frontend->save('data_two', 'cache_key_two', ['unrelated_tag']);
        $this->frontend->save('data_three', 'cache_key_three', ['tag_four']);

        $this->frontend->clean($this->frontend::CLEAN_MATCHING_TAG, []);

        $this->assertEquals(
            [
                'data_two',
                'data_three'
            ],
            [
                $this->frontend->load('cache_key_two'),
                $this->frontend->load('cache_key_three'),
            ],
            'Clearing cache by tag removed un-related records'
        );
    }
    
    /** @test */
    public function clearingCacheByAnyTagRemovesRecordsThatContainAtLeastOneOfTheMatchedTags()
    {
        $this->frontend->save('data_one', 'cache_key_one', ['tag_one', 'unrelated_tag', 'tag_four']);
        $this->frontend->save('data_two', 'cache_key_two', ['unrelated_tag']);
        $this->frontend->save('data_three', 'cache_key_three', ['tag_four']);
        $this->frontend->save('data_four', 'cache_key_four', ['tag_four', 'tag_one']);
        $this->frontend->save('data_five', 'cache_key_five', ['tag_one']);

        $this->frontend->clean($this->frontend::CLEAN_MATCHING_ANY_TAG, ['tag_one', 'tag_four']);

        $this->assertEquals(
            [
                false,
                'data_two',
                false,
                false,
                false
            ],
            [
                $this->frontend->load('cache_key_one'),
                $this->frontend->load('cache_key_two'),
                $this->frontend->load('cache_key_three'),
                $this->frontend->load('cache_key_four'),
                $this->frontend->load('cache_key_five'),
            ],
            'Clearing cache by tag removed un-related records'
        );
    }
    
    /** @test */
    public function invalidatesCacheThatIsExpired()
    {
        $this->frontend->save('expired_data', 'expired_cache_key', [], 0.005);
        usleep(6000);

        $this->assertEquals(false, $this->frontend->load('expired_cache_key'));
    }

    /** @test */
    public function keepsCacheWithLifeTimeThatExpiresInFuture()
    {
        $this->frontend->save('non_expired_data', 'not_expired_cache_key', [], 0.005);

        $this->assertEquals('non_expired_data', $this->frontend->load('not_expired_cache_key'));
    }


    /** @test */
    public function throwsNotSupportedErrorWhenBackendIsAccessed()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$this->getBackend() is not supported by InMemoryCache cache frontend.');

        $this->frontend->getBackend();
    }

    /** @test */
    public function throwsNotSupportedErrorWhenLowLevelFrontendIsAccessed()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$this->getLowLevelFrontend() is not supported by InMemoryCache cache frontend.');

        $this->frontend->getLowLevelFrontend();
    }
}
