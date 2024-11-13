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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param array $options
     *
     * @return void
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options): void
    {
        if ($options['adapter']!='' && is_callable($options['adapter'])) {
            $options['adapter'] = $options['adapter']($this);
        }
        $this->expectException('Zend_Cache_Exception');
        $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );
    }

    /**
     * @return array
     */
    public static function initializeWithExceptionDataProvider(): array
    {
        return [
            'empty_adapter' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => 'data_table',
                    'data_table_callback' => 'data_table_callback',
                    'tags_table' => 'tags_table',
                    'tags_table_callback' => 'tags_table_callback',
                    'adapter' => ''
                ]
            ],
            'empty_data_table' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => '',
                    'data_table_callback' => '',
                    'tags_table' => 'tags_table',
                    'tags_table_callback' => 'tags_table_callback',
                    'adapter' => static fn (self $testCase) => $testCase->createMock(Mysql::class)
                ]
            ],
            'empty_tags_table' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => 'data_table',
                    'data_table_callback' => 'data_table_callback',
                    'tags_table' => '',
                    'tags_table_callback' => '',
                    'adapter' => static fn (self $testCase) => $testCase->createMock(Mysql::class)
                ]
            ]
        ];
    }

    /**
     * @param \Closure $options
     * @param bool|string $expected
     *
     * @return void
     * @dataProvider loadDataProvider
     */
    public function testLoad($options, $expected): void
    {
        $options = $options($this);
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->load(5));
        $this->assertEquals($expected, $database->load(5, true));
    }

    protected function getMockForMysqlClass()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchOne'])
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

        return $connectionMock;
    }

    /**
     * @return array
     */
    public static function loadDataProvider(): array
    {
        $connectionMock = static fn (self $testCase) => $testCase->getMockForMysqlClass();

        return [
            'with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData($connectionMock),
                'expected' => 'loaded_value'

            ],
            'without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData(),
                'expected' => false
            ]
        ];
    }

    /**
     * @param Mysql|\Closure $connectionMock
     * @return array
     */
    public function getOptionsWithStoreData($connectionMock): array
    {
        if (is_callable($connectionMock)) {
            $connectionMock = $connectionMock($this);
        }

        return [
            'adapter_callback' => '',
            'data_table' => 'data_table',
            'data_table_callback' => 'data_table_callback',
            'tags_table' => 'tags_table',
            'tags_table_callback' => 'tags_table_callback',
            'store_data' => 'store_data',
            'adapter' => $connectionMock
        ];
    }

    /**
     * @param null|Mysql|MockObject|\Closure $connectionMock
     * @return array
     */
    public function getOptionsWithoutStoreData($connectionMock = null): array
    {
        if (is_callable($connectionMock)) {
            $connectionMock = $connectionMock($this);
        }
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
     * @return void
     * @dataProvider loadDataProvider
     */
    public function testTest($options, $expected): void
    {
        $options = $options($this);
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
     * @return void
     * @dataProvider saveDataProvider
     */
    public function testSave($options, $expected): void
    {
        $options = $options($this);
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
    public static function saveDataProvider(): array
    {
        return [
            'major_case_with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData(static fn (self $testCase) => $testCase->getSaveAdapterMock(true)),
                'expected' => true
            ],
            'minor_case_with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData(static fn (self $testCase) => $testCase->getSaveAdapterMock(false)),
                'expected' => false
            ],
            'without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData(),
                'expected' => true
            ]
        ];
    }

    /**
     * @param bool $result
     * @return Mysql|MockObject
     */
    protected function getSaveAdapterMock($result): Mysql
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['quoteIdentifier', 'query'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbStatementMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->onlyMethods(['rowCount'])
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
     * @return void
     * @dataProvider removeDataProvider
     */
    public function testRemove($options, $expected): void
    {
        $options = $options($this);
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->remove(3));
    }

    protected function getMockForMysqlClassTwo()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())
            ->method('delete')
            ->willReturn(true);
        return $connectionMock;
    }

    /**
     * @return array
     */
    public static function removeDataProvider(): array
    {
        $connectionMock = static fn (self $testCase) => $testCase->getMockForMysqlClassTwo();

        return [
            'with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData($connectionMock),
                'expected' => true

            ],
            'without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData(),
                'expected' => false
            ]
        ];
    }

    /**
     * @param array $options
     * @param string $mode
     * @param bool $expected
     *
     * @return void
     * @dataProvider cleanDataProvider
     */
    public function testClean($options, $mode, $expected): void
    {
        $options = $options($this);
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->clean($mode));
    }

    protected function getMockForMysqlClassThree()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['query', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())
            ->method('query')
            ->willReturn(false);

        $connectionMock->expects($this->any())
            ->method('delete')
            ->willReturn(true);

        return $connectionMock;
    }
    /**
     * @return array
     */
    public static function cleanDataProvider(): array
    {
        $connectionMock = static fn (self $testCase) => $testCase->getMockForMysqlClassThree();

        return [
            'mode_all_with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_ALL,
                'expected' => false

            ],
            'mode_all_without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_ALL,
                'expected' => false
            ],
            'mode_old_with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_OLD,
                'expected' => true

            ],
            'mode_old_without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_OLD,
                'expected' => true
            ],
            'mode_matching_tag_without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                'expected' => true
            ],
            'mode_not_matching_tag_without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                'expected' => true
            ],
            'mode_matching_any_tag_without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData($connectionMock),
                'mode' => \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                'expected' => true
            ]
        ];
    }

    /**
     * @return void
     */
    public function testCleanException(): void
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
     * @param \Closure $options
     * @param array $expected
     *
     * @return void
     * @dataProvider getIdsDataProvider
     */
    public function testGetIds($options, $expected): void
    {
        $options = $options($this);
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->getIds());
    }

    protected function getMockForMysqlClassFour()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchCol'])
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
        return $connectionMock;
    }

    /**
     * @return array
     */
    public static function getIdsDataProvider(): array
    {
        $connectionMock = static fn (self $testCase) => $testCase->getMockForMysqlClassFour();
        return [
            'with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData($connectionMock),
                'expected' => ['value_one', 'value_two']
            ],
            'without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData(),
                'expected' => []
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetTags(): void
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchCol'])
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

    /**
     * @return void
     */
    public function testGetIdsMatchingTags(): void
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchCol'])
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

    /**
     * @return void
     */
    public function testGetIdsNotMatchingTags(): void
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchCol'])
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

        $connectionMock
            ->method('fetchCol')
            ->willReturnOnConsecutiveCalls(['some_value_one'], ['some_value_two']);

        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $this->getOptionsWithStoreData($connectionMock)]
        );

        $this->assertEquals(['some_value_one'], $database->getIdsNotMatchingTags());
    }

    /**
     * @return void
     */
    public function testGetIdsMatchingAnyTags(): void
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchCol'])
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

    /**
     * @return void
     */
    public function testGetMetadatas(): void
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'fetchCol', 'fetchRow'])
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
                'tags' => ['some_value_one', 'some_value_two']
            ],
            $database->getMetadatas(5)
        );
    }

    /**
     * @param array $options
     * @param bool $expected
     *
     * @return void
     * @dataProvider touchDataProvider
     */
    public function testTouch($options, $expected): void
    {
        $options = $options($this);
        /** @var Database $database */
        $database = $this->objectManager->getObject(
            Database::class,
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->touch(2, 100));
    }

    protected function getMockForMysqlClassFive()
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())
            ->method('update')
            ->willReturn(false);

        return $connectionMock;
    }

    /**
     * @return array
     */
    public static function touchDataProvider(): array
    {
        $connectionMock = static fn (self $testCase) => $testCase->getMockForMysqlClassFive();

        return [
            'with_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithStoreData($connectionMock),
                'expected' => false

            ],
            'without_store_data' => [
                'options' => static fn (self $testCase) => $testCase->getOptionsWithoutStoreData(),
                'expected' => true
            ]
        ];
    }
}
