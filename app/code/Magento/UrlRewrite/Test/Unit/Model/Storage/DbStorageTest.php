<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     */
    public function testFindAllByData(): void
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->select
            ->method('where')
            ->withConsecutive(['col1 IN (?)', 'val1'], ['col2 IN (?)', 'val2']);

        $this->connectionMock
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([['row1'], ['row2']]);

        $this->dataObjectHelper
            ->method('populateWithArray')
            ->withConsecutive(
                [['urlRewrite1'], ['row1'], UrlRewrite::class],
                [['urlRewrite2'], ['row2'], UrlRewrite::class]
            )
            ->willReturnOnConsecutiveCalls($this->dataObjectHelper, $this->dataObjectHelper);

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
            ->withConsecutive(['col1 IN (?)', 'val1'], ['col2 IN (?)', 'val2']);

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
            ->withConsecutive(
                ['col1 IN (?)', 'val1'],
                ['col2 IN (?)', 'val2'],
                ['request_path IN (?)', [$origRequestPath, $origRequestPath . '/']]
            );

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
            ->withConsecutive(
                ['col1 IN (?)', 'val1'],
                ['col2 IN (?)', 'val2'],
                ['request_path IN (?)', [$origRequestPath, $origRequestPath . '/']]
            );

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
            ->withConsecutive(
                ['col1 IN (?)', 'val1'],
                ['col2 IN (?)', 'val2'],
                ['request_path IN (?)', [rtrim($origRequestPath, '/'), rtrim($origRequestPath, '/') . '/']]
            );

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
            ->withConsecutive(
                ['col1 IN (?)', 'val1'],
                ['col2 IN (?)', 'val2'],
                ['request_path IN (?)', [$origRequestPath, $origRequestPath . '/']]
            );

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
            ->withConsecutive(
                ['col1 IN (?)', 'val1'],
                ['col2 IN (?)', 'val2'],
                ['request_path IN (?)', [$origRequestPath, $origRequestPath . '/']]
            );

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
        $urlFirst->method('getEntityType')
            ->willReturn('product');
        $urlFirst->method('getEntityId')
            ->willReturn('entity_1');
        $urlFirst->method('getStoreId')
            ->willReturn('store_id_1');

        $urlSecond->method('getEntityType')
            ->willReturn('category');
        $urlSecond->method('getEntityId')
            ->willReturn('entity_2');
        $urlSecond->method('getStoreId')
            ->willReturn('store_id_2');

        $this->connectionMock->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->select->method($this->anything())
            ->willReturnSelf();

        $this->resource->method('getTableName')
            ->with(DbStorage::TABLE_NAME)
            ->willReturn('table_name');

        // insert

        $urlFirst->method('toArray')
            ->willReturn(['row1']);
        $urlSecond->method('toArray')
            ->willReturn(['row2']);

        $this->resource->method('getTableName')
            ->with(DbStorage::TABLE_NAME)
            ->willReturn('table_name');

        $urls = [$urlFirst, $urlSecond];

        $this->assertEquals($urls, $this->storage->replace($urls));
    }

    /**
     * @return void
     */
    public function testReplaceIfThrewExceptionOnDuplicateUrl(): void
    {
        $this->expectException('Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException');
        $url = $this->createMock(UrlRewrite::class);

        $url->method('toArray')
            ->willReturn(['row1']);

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->willThrowException(
                new Exception('SQLSTATE[23000]: test: 1062 test', DbStorage::ERROR_CODE_DUPLICATE_ENTRY)
            );
        $conflictingUrl = [
            UrlRewrite::URL_REWRITE_ID => 'conflicting-url'
        ];
        $this->connectionMock
            ->method('fetchRow')
            ->willReturn($conflictingUrl);

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
        $this->expectExceptionMessage('SQLSTATE[23000]: test: 1062 test');
        $url = $this->createMock(UrlRewrite::class);

        $url->method('toArray')
            ->willReturn(['row1']);

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->willThrowException(
                new Exception('SQLSTATE[23000]: test: 1062 test', DbStorage::ERROR_CODE_DUPLICATE_ENTRY)
            );

        $this->storage->replace([$url]);
    }

    /**
     * @return void
     */
    public function testReplaceIfThrewCustomException(): void
    {
        $this->expectException('RuntimeException');
        $url = $this->createMock(UrlRewrite::class);

        $url->method('toArray')
            ->willReturn(['row1']);

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->willThrowException(new \RuntimeException());

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
            ->withConsecutive(['col1 IN (?)', 'val1'], ['col2 IN (?)', 'val2']);
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
}
