<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param array $options
     *
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options)
    {
        $this->expectException('Zend_Cache_Exception');
        $this->objectManager->getObject(
            Database::class,
            [
                'options' => $options,
            ]
        );
    }

    /**
     * @return array
     */
    public function initializeWithExceptionDataProvider()
    {
        return [
            'empty_adapter' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => 'data_table',
                    'data_table_callback' => 'data_table_callback',
                    'tags_table' => 'tags_table',
                    'tags_table_callback' => 'tags_table_callback',
                    'adapter' => '',
                ],
            ],
            'empty_data_table' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => '',
                    'data_table_callback' => '',
                    'tags_table' => 'tags_table',
                    'tags_table_callback' => 'tags_table_callback',
                    'adapter' => $this->createMock(Mysql::class),
                ],
            ],
            'empty_tags_table' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => 'data_table',
                    'data_table_callback' => 'data_table_callback',
                    'tags_table' => '',
                    'tags_table_callback' => '',
                    'adapter' => $this->createMock(Mysql::class),
                ],
            ],
        ];
    }

    /**
     * @param array $options
     * @param bool|string $expected
     *
     * @dataProvider loadDataProvider
     */
    public function testLoad($options, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->load(5));
        $this->assertEquals($expected, $database->load(5, true));
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchOne'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(Select::class, ['where', 'from']);

        $selectMock->expects($this->any())
            ->method('where')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->any())
            ->method('fetchOne')
            ->willReturn('loaded_value');

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($connectionMock),
                'expected' => 'loaded_value',

            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => false,
            ],
        ];
    }

    /**
     * @param Mysql|MockObject $connectionMock
     * @return array
     */
    public function getOptionsWithStoreData($connectionMock)
    {
        return [
            'adapter_callback' => '',
            'data_table' => 'data_table',
            'data_table_callback' => 'data_table_callback',
            'tags_table' => 'tags_table',
            'tags_table_callback' => 'tags_table_callback',
            'store_data' => 'store_data',
            'adapter' => $connectionMock,
        ];
    }

    /**
     * @param null|Mysql|MockObject $connectionMock
     * @return array
     */
    public function getOptionsWithoutStoreData($connectionMock = null)
    {
        if (null === $connectionMock) {
            $connectionMock = $this->getMockBuilder(Mysql::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return [
            'adapter_callback' => '',
            'data_table' => 'data_table',
            'data_table_callback' => 'data_table_callback',
            'tags_table' => 'tags_table',
            'tags_table_callback' => 'tags_table_callback',
            'store_data' => '',
            'adapter' => $connectionMock
        ];
    }

    /**
     * @param array $options
     * @param bool|string $expected
     *
     * @dataProvider loadDataProvider
     */
    public function testTest($options, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->load(5));
        $this->assertEquals($expected, $database->load(5, true));
    }

    /**
     * @param array $options
     * @param bool $expected
     *
     * @dataProvider saveDataProvider
     */
    public function testSave($options, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->save('data', 4));
    }

    /**
     * @return array
     */
    public function saveDataProvider()
    {
        return [
            'major_case_with_store_data' => [
                'options' => $this->getOptionsWithStoreData($this->getSaveAdapterMock(true)),
                'expected' => true,
            ],
            'minor_case_with_store_data' => [
                'options' => $this->getOptionsWithStoreData($this->getSaveAdapterMock(false)),
                'expected' => false,
            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => true,
            ],
        ];
    }

    /**
     * @param bool $result
     * @return Mysql|MockObject
     */
    protected function getSaveAdapterMock($result)
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['quoteIdentifier', 'query'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbStatementMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->setMethods(['rowCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $dbStatementMock->expects($this->any())
            ->method('rowCount')
            ->willReturn($result);

        $connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturn('data');

        $connectionMock->expects($this->any())
            ->method('query')
            ->willReturn($dbStatementMock);

        return $connectionMock;
    }

    /**
     * @param array $options
     * @param bool $expected
     *
     * @dataProvider removeDataProvider
     */
    public function testRemove($options, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->remove(3));
    }

    /**
     * @return array
     */
    public function removeDataProvider()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($connectionMock),
                'expected' => true,

            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => false,
            ],
        ];
    }

    /**
     * @param array $options
     * @param string $mode
     * @param bool $expected
     *
     * @dataProvider cleanDataProvider
     */
    public function testClean($options, $mode, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->clean($mode));
    }

    /**
     * @return array
     */
    public function cleanDataProvider()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['query', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())
            ->method('query')
            ->willReturn(false);

        $connectionMock->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        return [
            'mode_all_with_store_data' => [
                'options' => $this->getOptionsWithStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_ALL,
                'expected' => false,

            ],
            'mode_all_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_ALL,
                'expected' => false,
            ],
            'mode_old_with_store_data' => [
                'options' => $this->getOptionsWithStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_OLD,
                'expected' => true,

            ],
            'mode_old_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_OLD,
                'expected' => true,
            ],
            'mode_matching_tag_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                'expected' => true,
            ],
            'mode_not_matching_tag_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                'expected' => true,
            ],
            'mode_matching_any_tag_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                'expected' => true,
            ],
        ];
    }

    public function testCleanException()
    {
        $this->expectException('Zend_Cache_Exception');
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithoutStoreData()]
        );

        $database->clean('my_unique_mode');
    }

    /**
     * @param array $options
     * @param array $expected
     *
     * @dataProvider getIdsDataProvider
     */
    public function testGetIds($options, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->getIds());
    }

    /**
     * @return array
     */
    public function getIdsDataProvider()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(Select::class, ['from']);

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->any())
            ->method('fetchCol')
            ->willReturn(['value_one', 'value_two']);

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($connectionMock),
                'expected' => ['value_one', 'value_two'],

            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => [],
            ],
        ];
    }

    public function testGetTags()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(Select::class, ['from', 'distinct']);

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('distinct')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->any())
            ->method('fetchCol')
            ->willReturn(['value_one', 'value_two']);

        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithStoreData($connectionMock)]
        );

        $this->assertEquals(['value_one', 'value_two'], $database->getIds());
    }

    public function testGetIdsMatchingTags()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(
            Select::class,
            ['from', 'distinct', 'where', 'group', 'having']
        );

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('distinct')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('where')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('group')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('having')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->any())
            ->method('fetchCol')
            ->willReturn(['value_one', 'value_two']);

        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithStoreData($connectionMock)]
        );

        $this->assertEquals(['value_one', 'value_two'], $database->getIdsMatchingTags());
    }

    public function testGetIdsNotMatchingTags()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(
            Select::class,
            ['from', 'distinct', 'where', 'group', 'having']
        );

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('distinct')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('where')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('group')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('having')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->at(1))
            ->method('fetchCol')
            ->willReturn(['some_value_one']);

        $connectionMock->expects($this->at(3))
            ->method('fetchCol')
            ->willReturn(['some_value_two']);

        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithStoreData($connectionMock)]
        );

        $this->assertEquals(['some_value_one'], $database->getIdsNotMatchingTags());
    }

    public function testGetIdsMatchingAnyTags()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(Select::class, ['from', 'distinct']);

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('distinct')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->any())
            ->method('fetchCol')
            ->willReturn(['some_value_one', 'some_value_two']);

        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithStoreData($connectionMock)]
        );

        $this->assertEquals(['some_value_one', 'some_value_two'], $database->getIds());
    }

    public function testGetMetadatas()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['select', 'fetchCol', 'fetchRow'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->createPartialMock(Select::class, ['from', 'where']);

        $selectMock->expects($this->any())
            ->method('from')->willReturnSelf();

        $selectMock->expects($this->any())
            ->method('where')->willReturnSelf();

        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $connectionMock->expects($this->any())
            ->method('fetchCol')
            ->willReturn(['some_value_one', 'some_value_two']);

        $connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn(['expire_time' => '3', 'update_time' => 2]);

        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithStoreData($connectionMock)]
        );

        $this->assertEquals(
            [
                'expire' => 3,
                'mtime' => 2,
                'tags' => ['some_value_one', 'some_value_two'],
            ],
            $database->getMetadatas(5)
        );
    }

    /**
     * @param array $options
     * @param bool $expected
     *
     * @dataProvider touchDataProvider
     */
    public function testTouch($options, $expected)
    {
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->touch(2, 100));
    }

    /**
     * @return array
     */
    public function touchDataProvider()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())
            ->method('update')
            ->willReturn(false);

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($connectionMock),
                'expected' => false,

            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => true,
            ],
        ];
    }
}
