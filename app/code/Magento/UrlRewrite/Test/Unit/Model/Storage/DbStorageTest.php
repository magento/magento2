<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model\Storage;

use Exception;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DbStorageTest extends TestCase
{
    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var DbStorage
     */
    private $storage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->createMock(ResourceConnection::class);

        $this->resource->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->method('select')
            ->willReturn($this->select);

        $this->storage = (new ObjectManager($this))->getObject(
            DbStorage::class,
            [
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'resource' => $this->resource
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testFindAllByData(): void
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->select
            ->method('where')
            ->willReturnCallback(function ($column, $value) {
                if ($column == 'col1 IN (?)' && $value == 'val1') {
                     return null;
                } elseif ($column == 'col2 IN (?)' && $value == 'val2') {
                    return null;
                }
            });

        $this->connectionMock
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([['row1'], ['row2']]);

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == ['urlRewrite1'] && $arg2 == ['row1'] && $arg3 == UrlRewrite::class) {
                    return $this->dataObjectHelper;
                } elseif ($arg1 == ['urlRewrite2'] && $arg2 == ['row2'] && $arg3 == UrlRewrite::class) {
                    return $this->dataObjectHelper;
                }
            });

        $this->urlRewriteFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls(['urlRewrite1'], ['urlRewrite2']);

        $this->assertEquals([['urlRewrite1'], ['urlRewrite2']], $this->storage->findAllByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByData(): void
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == ['col1 IN (?)', 'val1'] && $arg2 == ['col2 IN (?)', 'val2']) {
                    return $this->dataObjectHelper;
                }
            });

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->with($this->select)
            ->willReturn(['row1']);

        $this->connectionMock->expects($this->never())->method('fetchAll');

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->with(['urlRewrite1'], ['row1'], UrlRewrite::class)
            ->willReturn($this->dataObjectHelper);

        $this->urlRewriteFactory
            ->method('create')
            ->willReturn(['urlRewrite1']);

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataWithRequestPath(): void
    {
        $origRequestPath = 'page-one';
        $data = [
            'col1' => 'val1',
            'col2' => 'val2',
            UrlRewrite::REQUEST_PATH => $origRequestPath
        ];

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == ['col1 IN (?)', 'val1'] && $arg2 == ['col2 IN (?)', 'val2']) {
                    return $this->dataObjectHelper;
                }
            });

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->never())
            ->method('fetchRow');

        $urlRewriteRowInDb = [
            UrlRewrite::REQUEST_PATH => $origRequestPath,
            UrlRewrite::TARGET_PATH => $origRequestPath,
            UrlRewrite::REDIRECT_TYPE => 0,
        ];

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([$urlRewriteRowInDb]);

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->with(['urlRewrite1'], $urlRewriteRowInDb, UrlRewrite::class)
            ->willReturn($this->dataObjectHelper);

        $this->urlRewriteFactory
            ->method('create')
            ->willReturn(['urlRewrite1']);

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataWithRequestPathIsDifferent(): void
    {
        $origRequestPath = 'page-one';
        $data = [
            'col1' => 'val1',
            'col2' => 'val2',
            UrlRewrite::REQUEST_PATH => $origRequestPath
        ];

        $this->select
            ->method('where')
                ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                    if ($arg1 === ['col1 IN (?)', 'val1'] && $arg2 === ['col2 IN (?)', 'val2']) {
                        return $this->dataObjectHelper;
                    }
                    if ($arg1 === ['request_path IN (?)', [$arg3, $arg3 . '/']]) {
                        return $this->dataObjectHelper;
                    }
                });

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->never())
            ->method('fetchRow');

        $urlRewriteRowInDb = [
            UrlRewrite::REQUEST_PATH => $origRequestPath . '/',
            UrlRewrite::TARGET_PATH => $origRequestPath . '/',
            UrlRewrite::REDIRECT_TYPE => 0,
            UrlRewrite::STORE_ID => 1
        ];

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([$urlRewriteRowInDb]);

        $urlRewriteRedirect = [
            'request_path' => $origRequestPath,
            'redirect_type' => 301,
            'store_id' => 1,
            'entity_type' => 'custom',
            'entity_id' => '0',
            'target_path' => $origRequestPath . '/',
            'description' => null,
            'is_autogenerated' => '0',
            'metadata' => null
        ];

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->with(['urlRewrite1'], $urlRewriteRedirect, UrlRewrite::class)
            ->willReturn($this->dataObjectHelper);

        $this->urlRewriteFactory
            ->method('create')
            ->willReturn(['urlRewrite1']);

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataWithRequestPathIsDifferent2(): void
    {
        $origRequestPath = 'page-one/';
        $data = [
            'col1' => 'val1',
            'col2' => 'val2',
            UrlRewrite::REQUEST_PATH => $origRequestPath
        ];

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) use ($origRequestPath) {
                if ($arg1 === ['col1 IN (?)', 'val1'] && $arg2 === ['col2 IN (?)', 'val2']) {
                    return $this->dataObjectHelper;
                }
                if ($arg1 === ['request_path IN (?)', [rtrim($origRequestPath, '/'),
                        rtrim($origRequestPath, '/') . '/']]) {
                    return $this->dataObjectHelper;
                }
            });

        $this->connectionMock
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->never())
            ->method('fetchRow');

        $urlRewriteRowInDb = [
            UrlRewrite::REQUEST_PATH => rtrim($origRequestPath, '/'),
            UrlRewrite::TARGET_PATH => rtrim($origRequestPath, '/'),
            UrlRewrite::REDIRECT_TYPE => 0,
            UrlRewrite::STORE_ID => 1
        ];

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([$urlRewriteRowInDb]);

        $urlRewriteRedirect = [
            'request_path' => $origRequestPath,
            'redirect_type' => 301,
            'store_id' => 1,
            'entity_type' => 'custom',
            'entity_id' => '0',
            'target_path' => rtrim($origRequestPath, '/'),
            'description' => null,
            'is_autogenerated' => '0',
            'metadata' => null
        ];

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->with(['urlRewrite1'], $urlRewriteRedirect, UrlRewrite::class)
            ->willReturn($this->dataObjectHelper);

        $this->urlRewriteFactory
            ->method('create')
            ->willReturn(['urlRewrite1']);

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataWithRequestPathIsRedirect(): void
    {
        $origRequestPath = 'page-one';
        $data = [
            'col1' => 'val1',
            'col2' => 'val2',
            UrlRewrite::REQUEST_PATH => $origRequestPath
        ];

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) use ($origRequestPath) {
                if ($arg1 === ['col1 IN (?)', 'val1'] && $arg2 === ['col2 IN (?)', 'val2']) {
                    return $this->dataObjectHelper;
                }
                if ($arg1 === ['request_path IN (?)', [rtrim($origRequestPath, '/'),
                        rtrim($origRequestPath, '/') . '/']]) {
                    return $this->dataObjectHelper;
                }
            });

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->never())
            ->method('fetchRow');

        $urlRewriteRowInDb = [
            UrlRewrite::REQUEST_PATH => $origRequestPath . '/',
            UrlRewrite::TARGET_PATH => 'page-A/',
            UrlRewrite::REDIRECT_TYPE => 301,
            UrlRewrite::STORE_ID => 1
        ];

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([$urlRewriteRowInDb]);

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->with(['urlRewrite1'], $urlRewriteRowInDb, UrlRewrite::class)
            ->willReturn($this->dataObjectHelper);

        $this->urlRewriteFactory
            ->method('create')
            ->willReturn(['urlRewrite1']);

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataWithRequestPathTwoResults(): void
    {
        $origRequestPath = 'page-one';
        $data = [
            'col1' => 'val1',
            'col2' => 'val2',
            UrlRewrite::REQUEST_PATH => $origRequestPath,
        ];

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) use ($origRequestPath) {
                if ($arg1 === ['col1 IN (?)', 'val1'] && $arg2 === ['col2 IN (?)', 'val2']) {
                    return $this->dataObjectHelper;
                }
                if ($arg1 === ['request_path IN (?)', [rtrim($origRequestPath, '/'),
                        rtrim($origRequestPath, '/') . '/']]) {
                    return $this->dataObjectHelper;
                }
            });

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->never())
            ->method('fetchRow');

        $urlRewriteRowInDb = [
            UrlRewrite::REQUEST_PATH => $origRequestPath . '/',
            UrlRewrite::TARGET_PATH => 'page-A/',
            UrlRewrite::REDIRECT_TYPE => 301,
            UrlRewrite::STORE_ID => 1
        ];

        $urlRewriteRowInDb2 = [
            UrlRewrite::REQUEST_PATH => $origRequestPath,
            UrlRewrite::TARGET_PATH => 'page-B/',
            UrlRewrite::REDIRECT_TYPE => 301,
            UrlRewrite::STORE_ID => 1
        ];

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([$urlRewriteRowInDb, $urlRewriteRowInDb2]);

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->with(['urlRewrite1'], $urlRewriteRowInDb2, UrlRewrite::class)
            ->willReturn($this->dataObjectHelper);

        $this->urlRewriteFactory
            ->method('create')
            ->willReturn(['urlRewrite1']);

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testReplace(): void
    {
        $urlFirst = $this->createMock(UrlRewrite::class);
        $urlSecond = $this->createMock(UrlRewrite::class);
        // delete
        $urlFirst->method('getEntityType')->willReturn('product');
        $urlFirst->method('getEntityId')->willReturn('1');
        $urlFirst->method('getStoreId')->willReturn('store_id_1');
        $urlFirst->method('getRequestPath')->willReturn('store_id_1.html');
        $urlSecond->method('getEntityType')->willReturn('category');
        $urlSecond->method('getEntityId')->willReturn('2');
        $urlSecond->method('getStoreId')->willReturn('store_id_2');
        $urlSecond->method('getRequestPath')->willReturn('store_id_2.html');
        $this->connectionMock->method('quoteIdentifier')->willReturnArgument(0);
        $this->select->method($this->anything())->willReturnSelf();
        $this->resource->method('getTableName')->with(DbStorage::TABLE_NAME)->willReturn('table_name');
        // insert
        $urlFirst->method('toArray')->willReturn(['row1']);
        $urlSecond->method('toArray')->willReturn(['row2']);
        $this->resource->method('getTableName')->with(DbStorage::TABLE_NAME)->willReturn('table_name');
        $urls = [$urlFirst, $urlSecond];
        $this->connectionMock->method('fetchOne')->willReturnOnConsecutiveCalls(false, false);
        $this->assertEquals($urls, $this->storage->replace($urls));
    }

    /**
     * @return void
     */
    public function testReplaceIfThrewExceptionOnDuplicateUrl(): void
    {
        $this->expectException('Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException');
        $url = $this->createMock(UrlRewrite::class);
        $url->method('toArray')->willReturn(['row1']);
        $url->method('getEntityType')->willReturn('product');
        $url->method('getEntityId')->willReturn('1');
        $url->method('getStoreId')->willReturn('store_id_1');
        $url->method('getRequestPath')->willReturn('store_id_1.html');
        $this->connectionMock->method('fetchALL')->willReturn([[
            'url_rewrite_id' => '5718',
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'store_id_1.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
            'store_id' => '1',
            'description' => null,
            'is_autogenerated' => '1',
            'metadata' => null,
        ]]);
        $this->connectionMock->method('fetchOne')->willReturnOnConsecutiveCalls(false, true);
        $this->storage->replace([$url]);
    }

    /**
     * Validates a case when DB errors on duplicate entry, but calculated URLs are not really duplicated.
     *
     * An example is when URL length exceeds length of the DB field, so URLs are trimmed and become conflicting.
     *
     * @return void
     */
    public function testReplaceIfThrewExceptionOnDuplicateEntry(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unique constraint violation found');
        $url = $this->createMock(UrlRewrite::class);
        $url->method('toArray')->willReturn(['row1']);
        $url->method('getEntityType')->willReturn('product');
        $url->method('getEntityId')->willReturn('1');
        $url->method('getStoreId')->willReturn('store_id_1');
        $url->method('getRequestPath')->willReturn('store_id_1.html');
        $this->connectionMock->method('fetchALL')->willReturn([]);
        $this->connectionMock->method('fetchOne')->willReturnOnConsecutiveCalls(false, true);
        $this->storage->replace([$url]);
    }

    /**
     * Validates a case when we try to insert multiple URL rewrites with same requestPath
     *
     * The Exception is different than duplicating an existing rewrite because there's no url_rewrite_id.
     *
     * @return void
     */
    public function testReplaceIfThrewExceptionOnDuplicateUrlInInput(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unique constraint violation found');
        $url = $this->createMock(UrlRewrite::class);
        $url->method('toArray')->willReturn(['row1']);
        $url->method('getEntityType')->willReturn('product');
        $url->method('getEntityId')->willReturn('1');
        $url->method('getStoreId')->willReturn('store_id_1');
        $url->method('getRequestPath')->willReturn('store_id_1.html');
        $url2 = $this->createMock(UrlRewrite::class);
        $url2->method('toArray')->willReturn(['row2']);
        $url2->method('getEntityType')->willReturn('product');
        $url2->method('getEntityId')->willReturn('2');
        $url2->method('getStoreId')->willReturn('store_id_1');
        $url2->method('getRequestPath')->willReturn('store_id_1.html');
        $this->connectionMock->method('fetchALL')->willReturn([]);
        $this->connectionMock->method('fetchOne')->willReturnOnConsecutiveCalls(false, false);
        $this->storage->replace([$url, $url2]);
    }

    /**
     * @return void
     */
    public function testReplaceIfThrewCustomException(): void
    {
        $this->expectException('RuntimeException');
        $url = $this->createMock(UrlRewrite::class);
        $url->method('toArray')->willReturn(['row1']);
        $url->method('getEntityType')->willReturn('product');
        $url->method('getEntityId')->willReturn('1');
        $url->method('getStoreId')->willReturn('store_id_1');
        $url->method('getRequestPath')->willReturn('store_id_1.html');
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->willThrowException(new \RuntimeException());
        $this->connectionMock->method('fetchOne')->willReturnOnConsecutiveCalls(false, false);
        $this->storage->replace([$url]);
    }

    /**
     * @return void
     */
    public function testDeleteByData(): void
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 === ['col1 IN (?)', 'val1'] && $arg2 === ['col2 IN (?)', 'val2']) {
                    return $this->dataObjectHelper;
                }
            });

        $this->select
            ->method('deleteFromSelect')
            ->with('table_name')
            ->willReturn('sql delete query');

        $this->resource->method('getTableName')
            ->with(DbStorage::TABLE_NAME)
            ->willReturn('table_name');

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with('sql delete query');

        $this->storage->deleteByData($data);
    }

    /**
     * Test that invalid UTF-8 sequences are rejected to prevent collation errors
     *
     * @dataProvider invalidRequestPathDataProvider
     * @param string $requestPath
     * @param string $description
     * @return void
     */
    public function testFindOneByDataRejectsInvalidUtf8Sequences(string $requestPath, string $description): void
    {
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => 1
        ];

        // Database should never be queried for invalid paths
        $this->connectionMock->expects($this->never())
            ->method('fetchAll');

        $this->connectionMock->expects($this->never())
            ->method('fetchRow');

        $result = $this->storage->findOneByData($data);

        $this->assertNull($result, "Failed for case: {$description}");
    }

    /**
     * Test that valid UTF-8 paths with normal characters work correctly
     *
     * @dataProvider validRequestPathDataProvider
     * @param string $requestPath
     * @param string $description
     * @return void
     */
    public function testFindOneByDataAcceptsValidUtf8Paths(string $requestPath, string $description): void
    {
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => 1
        ];

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->select->method('where')
            ->willReturnSelf();

        // Database should be queried normally
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([]);

        $result = $this->storage->findOneByData($data);

        // Result should be null (no matching URL found), but query should have executed
        $this->assertNull($result, "Failed for case: {$description}");
    }

    /**
     * Data provider for invalid request paths that should be rejected
     *
     * @return array
     */
    public function invalidRequestPathDataProvider(): array
    {
        return [
            // Path traversal attempts with overlong UTF-8 encoding (invalid UTF-8)
            [
                '%c0%ae%c0%ae/%c0%ae%c0%ae/%c0%ae%c0%ae/%c0%ae%c0%ae/etc/passwd',
                'Path traversal with overlong encoding (%c0%ae) - invalid UTF-8'
            ],
            // Invalid single UTF-8 byte
            [
                '%C0',
                'Invalid single UTF-8 byte'
            ],
            // Emojis (4-byte UTF-8 characters that cause collation issues)
            [
                '🔎',
                'Magnifying glass emoji (U+1F50E) - 4-byte UTF-8'
            ],
            [
                'search/🔎',
                'Emoji in path segment - 4-byte UTF-8'
            ],
            [
                '😀',
                'Grinning face emoji (U+1F600) - 4-byte UTF-8'
            ],
            [
                '🎉',
                'Party popper emoji (U+1F389) - 4-byte UTF-8'
            ],
            // Control characters
            [
                "test\x00path",
                'Null byte in path'
            ],
            [
                "test\x1Fpath",
                'Unit separator control character'
            ],
            [
                "test\x7Fpath",
                'DEL control character'
            ],
            // Mixed invalid sequences
            [
                '%c0%ae%c0%ae/🔎/test',
                'Overlong encoding with emoji - invalid UTF-8'
            ],
            // More 4-byte UTF-8 characters
            [
                '𝕳𝖊𝖑𝖑𝖔',
                'Mathematical alphanumeric symbols (U+1D573-U+1D586) - 4-byte UTF-8'
            ],
            [
                '🏠',
                'House emoji (U+1F3E0) - 4-byte UTF-8'
            ],
        ];
    }

    /**
     * Data provider for valid request paths that should be accepted
     *
     * @return array
     */
    public function validRequestPathDataProvider(): array
    {
        return [
            // Standard ASCII paths
            [
                'products/laptop',
                'Simple product path'
            ],
            [
                'category/electronics/computers',
                'Multi-level category path'
            ],
            [
                'about-us',
                'Simple CMS page'
            ],
            // Valid 3-byte UTF-8 characters (should work)
            [
                'café-menu',
                'French accented character (é)'
            ],
            [
                'products/niño',
                'Spanish ñ character'
            ],
            [
                'münchen-store',
                'German umlaut (ü)'
            ],
            [
                'товар',
                'Cyrillic characters'
            ],
            [
                '产品',
                'Chinese characters (3-byte UTF-8)'
            ],
            [
                'المنتجات',
                'Arabic characters'
            ],
            // Special URL-safe characters
            [
                'product-name-123',
                'Hyphens and numbers'
            ],
            [
                'product_name_test',
                'Underscores'
            ],
            [
                'category/sub.category',
                'Dots in path'
            ],
            [
                'path/to/page',
                'Forward slashes'
            ],
            // URL-encoded valid characters
            [
                'product%20name',
                'URL-encoded space'
            ],
            [
                'category%2Fsubcategory',
                'URL-encoded forward slash'
            ],
        ];
    }
}
