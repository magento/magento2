<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\Cache\Type\Layout;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    /**
     * @var FrontendInterface|MockObject
     */
    private $cacheFrontendMock;

    /**
     * @var Layout
     */
    private $layoutCacheType;

    protected function setUp(): void
    {
        $this->cacheFrontendMock = $this->createMock(FrontendInterface::class);
        $frontendPoolMock = $this->createMock(FrontendPool::class);
        $frontendPoolMock->method('get')
            ->with(Layout::TYPE_IDENTIFIER)
            ->willReturn($this->cacheFrontendMock);

        $this->layoutCacheType = new Layout($frontendPoolMock);
    }

    /**
     * @param string $data
     * @param string $identifier
     * @return void
     * @dataProvider dataProviderForSaveDataTest
     */
    public function testSaveData(string $data, string $identifier)
    {
        $tags = ['tag1', 'tag2'];
        $lifeTime = 1000;

        $dataHash = hash(Layout::HASH_TYPE, $data);
        $identifierForHash = Layout::HASH_PREFIX . $dataHash;

        $expectedTags = array_merge($tags, [Layout::CACHE_TAG]);
        $invocation = 0;

        $this->cacheFrontendMock->method('save')
            ->willReturnCallback(
                function (
                    $passedData,
                    $passedIdentifier,
                    $passedTags,
                    $passedLifeTime
                ) use (
                    &$invocation,
                    $data,
                    $identifierForHash,
                    $expectedTags,
                    $lifeTime,
                    $identifier
                ) {
                    if ($invocation === 0) {
                        $this->assertEquals($data, $passedData);
                        $this->assertEquals($identifierForHash, $passedIdentifier);
                        $this->assertEquals($expectedTags, $passedTags);
                        $this->assertEquals(Layout::DATA_LIFETIME, $passedLifeTime);
                    } elseif ($invocation === 1) {
                        $this->assertEquals($identifierForHash, $passedData);
                        $this->assertEquals($identifier, $passedIdentifier);
                        $this->assertEquals($expectedTags, $passedTags);
                        $this->assertEquals($lifeTime, $passedLifeTime);
                    }
                    $invocation++;
                    return true;
                }
            );

        $result = $this->layoutCacheType->save($data, $identifier, $tags, $lifeTime);
        $this->assertTrue($result);
    }

    /**
     * Data provider for the testSaveData test.
     */
    public function dataProviderForSaveDataTest(): array
    {
        return [
            'Test with normal data' => ['test-data', 'test-identifier'],
            'Test with empty string' => ['', 'empty-string-identifier'],
        ];
    }

    /**
     * @param string $identifier
     * @param bool|string|null $firstLoadReturn
     * @param string|null $secondLoadReturn
     * @param bool|string|null $expectedResult
     * @return void
     * @dataProvider loadDataProvider
     */
    public function testLoadData(
        string $identifier,
        bool|string|null $firstLoadReturn,
        ?string $secondLoadReturn,
        bool|string|null $expectedResult
    ) {
        $this->cacheFrontendMock->method('load')
            ->willReturnMap([
                [$identifier, $firstLoadReturn],
                [$firstLoadReturn, $secondLoadReturn]
            ]);

        $result = $this->layoutCacheType->load($identifier);
        $this->assertEquals($expectedResult, $result);
    }

    public function loadDataProvider(): array
    {
        $identifier = 'test-identifier';
        $hashedValue = Layout::HASH_PREFIX . hash(Layout::HASH_TYPE, 'test-data');
        $rawData = 'raw-test-data';
        $nonExistentIdentifier = 'non-existent-identifier';

        return [
            // Test with hash prefix and successful nested load
            [
                'identifier' => $identifier,
                'firstLoadReturn' => $hashedValue,
                'secondLoadReturn' => 'test-data',
                'expectedResult' => 'test-data',
            ],
            // Test when identifier does not exist
            [
                'identifier' => $nonExistentIdentifier,
                'firstLoadReturn' => false,
                'secondLoadReturn' => null, // Not used, since first load returns false
                'expectedResult' => false,
            ],
            // Test with null value, assuming null is an acceptable return value
            [
                'identifier' => $nonExistentIdentifier,
                'firstLoadReturn' => null,
                'secondLoadReturn' => null, // Not used, since first load returns null
                'expectedResult' => null,
            ],
            // Test without hash prefix
            [
                'identifier' => $identifier,
                'firstLoadReturn' => $rawData,
                'secondLoadReturn' => null, // Not used, since no hash prefix is present
                'expectedResult' => $rawData,
            ],
        ];
    }
}
