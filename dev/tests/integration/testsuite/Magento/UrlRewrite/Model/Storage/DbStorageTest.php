<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model\Storage;

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite as UrlRewriteFixture;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for DbStorage to verify UTF-8mb4 character support
 */
#[
    AppIsolation(true),
    DbIsolation(true)
]
class DbStorageTest extends TestCase
{
    /**
     * @var DbStorage
     */
    private $storage;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->storage = $objectManager->get(DbStorage::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->urlRewriteFactory = $objectManager->get(UrlRewriteFactory::class);
    }

    /**
     * Test that url_rewrite table supports utf8mb4 charset for 4-byte UTF-8 characters
     *
     * @return void
     */
    public function testTableCharsetIsUtf8mb4OrUtf8(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('url_rewrite');

        // Get table status
        $tableStatus = $connection->fetchRow("SHOW TABLE STATUS LIKE '{$tableName}'");

        $this->assertNotEmpty($tableStatus, 'Table url_rewrite should exist');

        // Check collation
        $collation = $tableStatus['Collation'] ?? '';

        // Should be either utf8mb4 (modern) or utf8 (legacy)
        $this->assertMatchesRegularExpression(
            '/^(utf8mb4|utf8)_/',
            $collation,
            'Table collation should be utf8mb4 or utf8'
        );
    }

    /**
     * Test inserting and querying URL rewrites with 4-byte UTF-8 characters (emojis)
     *
     * This test verifies that:
     * 1. Can insert URL rewrites with emojis when database supports utf8mb4
     * 2. Can query URL rewrites with emojis
     * 3. Returns correct results with emoji matching
     *
     * @dataProvider utf8mb4RequestPathDataProvider
     * @param string $requestPath
     * @param string $expectedTargetPath
     * @param string $description
     * @return void
     */
    #[
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'search/🔎/products', 'target_path' => 'catalog/search/results', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'celebrate/🎉', 'target_path' => 'cms/party', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'emoji/😀/happy', 'target_path' => 'cms/happiness', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'home/🏠', 'target_path' => 'cms/index/index', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'math/𝕳𝖊𝖑𝖑𝖔', 'target_path' => 'cms/math/hello', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'special/café', 'target_path' => 'cms/cafe', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'chinese/你好', 'target_path' => 'cms/hello', 'store_id' => 1]
        )
    ]
    public function testFindOneByDataWithUtf8mb4Characters(
        string $requestPath,
        string $expectedTargetPath,
        string $description
    ): void {
        // Check if database supports utf8mb4
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('url_rewrite');
        $tableStatus = $connection->fetchRow("SHOW TABLE STATUS LIKE '{$tableName}'");
        $collation = $tableStatus['Collation'] ?? '';

        if (!str_starts_with($collation, 'utf8mb4')) {
            $this->markTestSkipped(
                "Test skipped: Database table uses '{$collation}' which doesn't support 4-byte UTF-8. "
                . "Requires utf8mb4 collation for emoji support."
            );
        }

        // Query for the URL rewrite
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => 1
        ];

        $result = $this->storage->findOneByData($data);

        $this->assertNotNull($result, "Failed to find URL rewrite for: {$description}");
        $this->assertInstanceOf(UrlRewrite::class, $result);
        $this->assertEquals($requestPath, $result->getRequestPath());
        $this->assertEquals($expectedTargetPath, $result->getTargetPath());
        $this->assertEquals(1, $result->getStoreId());
    }

    /**
     * Test that utf8mb3 characters work in both utf8 and utf8mb4
     *
     * @dataProvider utf8RequestPathDataProvider
     * @param string $requestPath
     * @param string $expectedTargetPath
     * @param string $description
     * @return void
     */
    #[
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'search/🔎/products', 'target_path' => 'catalog/search/results', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'celebrate/🎉', 'target_path' => 'cms/party', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'emoji/😀/happy', 'target_path' => 'cms/happiness', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'home/🏠', 'target_path' => 'cms/index/index', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'math/𝕳𝖊𝖑𝖑𝖔', 'target_path' => 'cms/math/hello', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'special/café', 'target_path' => 'cms/cafe', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'chinese/你好', 'target_path' => 'cms/hello', 'store_id' => 1]
        )
    ]
    public function testFindOneByDataWithUtf8mb3Characters(
        string $requestPath,
        string $expectedTargetPath,
        string $description
    ): void {
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => 1
        ];

        $result = $this->storage->findOneByData($data);

        $this->assertNotNull($result, "Failed to find URL rewrite for: {$description}");
        $this->assertInstanceOf(UrlRewrite::class, $result);
        $this->assertEquals($requestPath, $result->getRequestPath());
        $this->assertEquals($expectedTargetPath, $result->getTargetPath());
    }

    /**
     * Test replacing URL rewrites with 4-byte UTF-8 characters
     *
     * @return void
     */
    public function testReplaceWithUtf8mb4Characters(): void
    {
        // Check if database supports utf8mb4
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('url_rewrite');
        $tableStatus = $connection->fetchRow("SHOW TABLE STATUS LIKE '{$tableName}'");
        $collation = $tableStatus['Collation'] ?? '';

        if (!str_starts_with($collation, 'utf8mb4')) {
            $this->markTestSkipped(
                "Test skipped: Database table uses '{$collation}' which doesn't support 4-byte UTF-8."
            );
        }

        // Create URL rewrite with emoji
        $urlRewrite = $this->urlRewriteFactory->create();
        $urlRewrite->setEntityType('custom')
            ->setEntityId(999)
            ->setRequestPath('test/🚀/rocket')
            ->setTargetPath('catalog/product/view/id/999')
            ->setRedirectType(0)
            ->setStoreId(1)
            ->setDescription('Test rocket emoji URL');

        // Replace (insert) the URL rewrite
        $result = $this->storage->replace([$urlRewrite]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        // Verify it was saved correctly
        $data = [
            UrlRewrite::REQUEST_PATH => 'test/🚀/rocket',
            UrlRewrite::STORE_ID => 1
        ];

        $found = $this->storage->findOneByData($data);
        $this->assertNotNull($found);
        $this->assertEquals('test/🚀/rocket', $found->getRequestPath());
        $this->assertEquals('catalog/product/view/id/999', $found->getTargetPath());

        // Cleanup
        $this->storage->deleteByData([
            UrlRewrite::REQUEST_PATH => ['test/🚀/rocket'],
            UrlRewrite::STORE_ID => [1]
        ]);
    }

    /**
     * Test findAllByData with 4-byte UTF-8 characters
     *
     * @return void
     */
    #[
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'search/🔎/products', 'target_path' => 'catalog/search/results', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'celebrate/🎉', 'target_path' => 'cms/party', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'emoji/😀/happy', 'target_path' => 'cms/happiness', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'home/🏠', 'target_path' => 'cms/index/index', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'math/𝕳𝖊𝖑𝖑𝖔', 'target_path' => 'cms/math/hello', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'special/café', 'target_path' => 'cms/cafe', 'store_id' => 1]
        ),
        DataFixture(
            UrlRewriteFixture::class,
            ['request_path' => 'chinese/你好', 'target_path' => 'cms/hello', 'store_id' => 1]
        )
    ]
    public function testFindAllByDataWithUtf8mb4Characters(): void
    {
        // Check if database supports utf8mb4
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('url_rewrite');
        $tableStatus = $connection->fetchRow("SHOW TABLE STATUS LIKE '{$tableName}'");
        $collation = $tableStatus['Collation'] ?? '';

        if (!str_starts_with($collation, 'utf8mb4')) {
            $this->markTestSkipped(
                "Test skipped: Database table uses '{$collation}' which doesn't support 4-byte UTF-8."
            );
        }

        $data = [
            UrlRewrite::STORE_ID => [1]
        ];

        $results = $this->storage->findAllByData($data);

        $this->assertIsArray($results);

        // Filter results to find our emoji URLs
        $emojiUrls = array_filter($results, function ($urlRewrite) {
            $requestPath = $urlRewrite->getRequestPath();
            return str_contains($requestPath, '🔎')
                || str_contains($requestPath, '🎉')
                || str_contains($requestPath, '😀')
                || str_contains($requestPath, '🏠');
        });

        $this->assertGreaterThanOrEqual(4, count($emojiUrls), 'Should find at least 4 emoji URLs');
    }

    /**
     * Data provider for 4-byte UTF-8 characters (requires utf8mb4)
     *
     * @return array
     */
    public static function utf8mb4RequestPathDataProvider(): array
    {
        return [
            [
                'search/🔎/products',
                'catalog/search/results',
                'Magnifying glass emoji (U+1F50E) - 4-byte UTF-8'
            ],
            [
                'celebrate/🎉',
                'cms/party',
                'Party popper emoji (U+1F389) - 4-byte UTF-8'
            ],
            [
                'emoji/😀/happy',
                'cms/happiness',
                'Grinning face emoji (U+1F600) - 4-byte UTF-8'
            ],
            [
                'home/🏠',
                'cms/index/index',
                'House emoji (U+1F3E0) - 4-byte UTF-8'
            ],
            [
                'math/𝕳𝖊𝖑𝖑𝖔',
                'cms/math/hello',
                'Mathematical alphanumeric symbols - 4-byte UTF-8'
            ],
        ];
    }

    /**
     * Data provider for 3-byte UTF-8 characters (works with both utf8 and utf8mb4)
     *
     * @return array
     */
    public static function utf8RequestPathDataProvider(): array
    {
        return [
            [
                'special/café',
                'cms/cafe',
                'Accented characters (3-byte UTF-8)'
            ],
            [
                'chinese/你好',
                'cms/hello',
                'Chinese characters (3-byte UTF-8)'
            ],
        ];
    }
}
