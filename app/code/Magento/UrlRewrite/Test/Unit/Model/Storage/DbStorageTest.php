<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\UrlRewrite\Test\Unit\Model\Storage;

use \Magento\UrlRewrite\Model\Storage\DbStorage;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\UrlRewrite\Model\Storage\DbStorage
     */
    protected $storage;

    protected function setUp()
    {
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->dataObjectHelper = $this->getMock(
            \Magento\Framework\Api\DataObjectHelper::class, [], [], '',
            false);
        $this->connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->select = $this->getMock(
            \Magento\Framework\DB\Select::class, ['from', 'where', 'deleteFromSelect'], [], '',
            false);
        $this->resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);

        $this->resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));

        $this->storage = (new ObjectManager($this))->getObject(\Magento\UrlRewrite\Model\Storage\DbStorage::class,
            [
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'resource' => $this->resource,
            ]
        );
    }

    public function testFindAllByData()
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->select->expects($this->at(1))
            ->method('where')
            ->with('col1 IN (?)', 'val1');

        $this->select->expects($this->at(2))
            ->method('where')
            ->with('col2 IN (?)', 'val2');

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->will($this->returnValue([['row1'], ['row2']]));

        $this->dataObjectHelper->expects($this->at(0))
            ->method('populateWithArray')
            ->with(['urlRewrite1'], ['row1'], \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->will($this->returnSelf());

        $this->urlRewriteFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue(['urlRewrite1']));

        $this->dataObjectHelper->expects($this->at(1))
            ->method('populateWithArray')
            ->with(['urlRewrite2'], ['row2'], \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->will($this->returnSelf());

        $this->urlRewriteFactory->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue(['urlRewrite2']));

        $this->assertEquals([['urlRewrite1'], ['urlRewrite2']], $this->storage->findAllByData($data));
    }

    public function testFindOneByData()
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->select->expects($this->at(1))
            ->method('where')
            ->with('col1 IN (?)', 'val1');

        $this->select->expects($this->at(2))
            ->method('where')
            ->with('col2 IN (?)', 'val2');

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->with($this->select)
            ->will($this->returnValue(['row1']));

        $this->dataObjectHelper->expects($this->at(0))
            ->method('populateWithArray')
            ->with(['urlRewrite1'], ['row1'], \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->will($this->returnSelf());

        $this->urlRewriteFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue(['urlRewrite1']));

        $this->assertEquals(['urlRewrite1'], $this->storage->findOneByData($data));
    }

    public function testReplace()
    {
        $urlFirst = $this->getMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class, [], [], '', false);
        $urlSecond = $this->getMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class, [], [], '', false);

        // delete

        $urlFirst->expects($this->any())
            ->method('getByKey')
            ->will($this->returnValueMap([
                [UrlRewrite::ENTITY_TYPE, 'product'],
                [UrlRewrite::ENTITY_ID, 'entity_1'],
                [UrlRewrite::STORE_ID, 'store_id_1'],
            ]));
        $urlFirst->expects($this->any())->method('getEntityType')->willReturn('product');
        $urlSecond->expects($this->any())
            ->method('getByKey')
            ->will($this->returnValueMap([
                [UrlRewrite::ENTITY_TYPE, 'category'],
                [UrlRewrite::ENTITY_ID, 'entity_2'],
                [UrlRewrite::STORE_ID, 'store_id_2'],
            ]));
        $urlSecond->expects($this->any())->method('getEntityType')->willReturn('category');

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));

        $this->select->expects($this->at(1))
            ->method('where')
            ->with('entity_id IN (?)', ['entity_1']);

        $this->select->expects($this->at(2))
            ->method('where')
            ->with('store_id IN (?)', ['store_id_1']);

        $this->select->expects($this->at(3))
            ->method('where')
            ->with('entity_type IN (?)', 'product');

        $this->select->expects($this->at(4))
            ->method('deleteFromSelect')
            ->with('table_name')
            ->will($this->returnValue('sql delete query'));

        $this->select->expects($this->at(6))
            ->method('where')
            ->with('entity_id IN (?)', ['entity_2']);

        $this->select->expects($this->at(7))
            ->method('where')
            ->with('store_id IN (?)', ['store_id_2']);

        $this->select->expects($this->at(8))
            ->method('where')
            ->with('entity_type IN (?)', 'category');

        $this->select->expects($this->at(9))
            ->method('deleteFromSelect')
            ->with('table_name')
            ->will($this->returnValue('sql delete query'));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->with(DbStorage::TABLE_NAME)
            ->will($this->returnValue('table_name'));

        $this->connectionMock->expects($this->any())
            ->method('query')
            ->with('sql delete query');

        // insert

        $urlFirst->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(['row1']));
        $urlSecond->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(['row2']));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->with(DbStorage::TABLE_NAME)
            ->will($this->returnValue('table_name'));

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->with('table_name', [['row1'], ['row2']]);

        $this->storage->replace([$urlFirst, $urlSecond]);
    }

    /**
     * @expectedException \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function testReplaceIfThrewExceptionOnDuplicateUrl()
    {
        $url = $this->getMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class, [], [], '', false);

        $url->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(['row1']));

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->will(
                $this->throwException(
                    new \Exception('SQLSTATE[23000]: test: 1062 test', DbStorage::ERROR_CODE_DUPLICATE_ENTRY)
                )
            );
        $conflictingUrl = [
            UrlRewrite::URL_REWRITE_ID => 'conflicting-url'
        ];
        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn($conflictingUrl);

        $this->storage->replace([$url]);
    }

    /**
     * Validates a case when DB errors on duplicate entry, but calculated URLs are not really duplicated
     *
     * An example is when URL length exceeds length of the DB field, so URLs are trimmed and become conflicting
     *
     * @expectedException \Exception
     * @expectedExceptionMessage SQLSTATE[23000]: test: 1062 test
     */
    public function testReplaceIfThrewExceptionOnDuplicateEntry()
    {
        $url = $this->getMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class, [], [], '', false);

        $url->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(['row1']));

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->will(
                $this->throwException(
                    new \Exception('SQLSTATE[23000]: test: 1062 test', DbStorage::ERROR_CODE_DUPLICATE_ENTRY)
                )
            );

        $this->storage->replace([$url]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReplaceIfThrewCustomException()
    {
        $url = $this->getMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class, [], [], '', false);

        $url->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(['row1']));

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->will($this->throwException(new \RuntimeException()));

        $this->storage->replace([$url]);
    }

    public function testDeleteByData()
    {
        $data = ['col1' => 'val1', 'col2' => 'val2'];

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));

        $this->select->expects($this->at(1))
            ->method('where')
            ->with('col1 IN (?)', 'val1');

        $this->select->expects($this->at(2))
            ->method('where')
            ->with('col2 IN (?)', 'val2');

        $this->select->expects($this->at(3))
            ->method('deleteFromSelect')
            ->with('table_name')
            ->will($this->returnValue('sql delete query'));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->with(DbStorage::TABLE_NAME)
            ->will($this->returnValue('table_name'));

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with('sql delete query');

        $this->storage->deleteByData($data);
    }
}
