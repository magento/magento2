<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Backend;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param array $options
     *
     * @expectedException \Zend_Cache_Exception
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options)
    {
        $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
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
                    'adapter' => $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false),
                ],
            ],
            'empty_tags_table' => [
                'options' => [
                    'adapter_callback' => '',
                    'data_table' => 'data_table',
                    'data_table_callback' => 'data_table_callback',
                    'tags_table' => '',
                    'tags_table_callback' => '',
                    'adapter' => $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false),
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
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
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
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchOne'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock('Magento\Framework\DB\Select', ['where', 'from'], [], '', false);

        $selectMock->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->any())
            ->method('fetchOne')
            ->will($this->returnValue('loaded_value'));

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($adapterMock),
                'expected' => 'loaded_value',

            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => false,
            ],
        ];
    }

    /**
     * @param \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject $adapterMock
     * @return array
     */
    public function getOptionsWithStoreData($adapterMock)
    {
        return [
            'adapter_callback' => '',
            'data_table' => 'data_table',
            'data_table_callback' => 'data_table_callback',
            'tags_table' => 'tags_table',
            'tags_table_callback' => 'tags_table_callback',
            'store_data' => 'store_data',
            'adapter' => $adapterMock,
        ];
    }

    /**
     * @param null|\Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject $adapterMock
     * @return array
     */
    public function getOptionsWithoutStoreData($adapterMock = null)
    {
        if (null === $adapterMock) {
            $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
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
            'adapter' => $adapterMock
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
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
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
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
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
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSaveAdapterMock($result)
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['quoteIdentifier', 'query'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbStatementMock = $this->getMockBuilder('Zend_Db_Statement_Interface')
            ->setMethods(['rowCount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $dbStatementMock->expects($this->any())
            ->method('rowCount')
            ->will($this->returnValue($result));

        $adapterMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnValue('data'));

        $adapterMock->expects($this->any())
            ->method('query')
            ->will($this->returnValue($dbStatementMock));

        return $adapterMock;
    }

    /**
     * @param array $options
     * @param bool $expected
     *
     * @dataProvider removeDataProvider
     */
    public function testRemove($options, $expected)
    {
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->remove(3));
    }

    /**
     * @return array
     */
    public function removeDataProvider()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $adapterMock->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($adapterMock),
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
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->clean($mode));
    }

    /**
     * @return array
     */
    public function cleanDataProvider()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['query', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $adapterMock->expects($this->any())
            ->method('query')
            ->will($this->returnValue(false));

        $adapterMock->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        return [
            'mode_all_with_store_data' => [
                'options' => $this->getOptionsWithStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_ALL,
                'expected' => false,

            ],
            'mode_all_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_ALL,
                'expected' => false,
            ],
            'mode_old_with_store_data' => [
                'options' => $this->getOptionsWithStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_OLD,
                'expected' => true,

            ],
            'mode_old_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_OLD,
                'expected' => true,
            ],
            'mode_matching_tag_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                'expected' => true,
            ],
            'mode_not_matching_tag_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                'expected' => true,
            ],
            'mode_matching_any_tag_without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData($adapterMock),
                'mode' => \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                'expected' => true,
            ],
        ];
    }

    /**
     * @expectedException \Zend_Cache_Exception
     */
    public function testCleanException()
    {
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
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
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->getIds());
    }

    /**
     * @return array
     */
    public function getIdsDataProvider()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock('Magento\Framework\DB\Select', ['from'], [], '', false);

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->any())
            ->method('fetchCol')
            ->will($this->returnValue(['value_one', 'value_two']));

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($adapterMock),
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
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock('Magento\Framework\DB\Select', ['from', 'distinct'], [], '', false);

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('distinct')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->any())
            ->method('fetchCol')
            ->will($this->returnValue(['value_one', 'value_two']));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $this->getOptionsWithStoreData($adapterMock)]
        );

        $this->assertEquals(['value_one', 'value_two'], $database->getIds());
    }

    public function testGetIdsMatchingTags()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['from', 'distinct', 'where', 'group', 'having'],
            [],
            '',
            false
        );

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('distinct')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('group')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('having')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->any())
            ->method('fetchCol')
            ->will($this->returnValue(['value_one', 'value_two']));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $this->getOptionsWithStoreData($adapterMock)]
        );

        $this->assertEquals(['value_one', 'value_two'], $database->getIdsMatchingTags());
    }

    public function testGetIdsNotMatchingTags()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['from', 'distinct', 'where', 'group', 'having'],
            [],
            '',
            false
        );

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('distinct')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('group')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('having')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->at(1))
            ->method('fetchCol')
            ->will($this->returnValue(['some_value_one']));

        $adapterMock->expects($this->at(3))
            ->method('fetchCol')
            ->will($this->returnValue(['some_value_two']));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $this->getOptionsWithStoreData($adapterMock)]
        );

        $this->assertEquals(['some_value_one'], $database->getIdsNotMatchingTags());
    }

    public function testGetIdsMatchingAnyTags()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchCol'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock('Magento\Framework\DB\Select', ['from', 'distinct'], [], '', false);

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('distinct')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->any())
            ->method('fetchCol')
            ->will($this->returnValue(['some_value_one', 'some_value_two']));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $this->getOptionsWithStoreData($adapterMock)]
        );

        $this->assertEquals(['some_value_one', 'some_value_two'], $database->getIds());
    }

    public function testGetMetadatas()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['select', 'fetchCol', 'fetchRow'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMock('Magento\Framework\DB\Select', ['from', 'where'], [], '', false);

        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $selectMock->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        $adapterMock->expects($this->any())
            ->method('fetchCol')
            ->will($this->returnValue(['some_value_one', 'some_value_two']));

        $adapterMock->expects($this->any())
            ->method('fetchRow')
            ->will($this->returnValue(['expire_time' => '3', 'update_time' => 2]));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $this->getOptionsWithStoreData($adapterMock)]
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
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            'Magento\Framework\Cache\Backend\Database',
            ['options' => $options]
        );

        $this->assertEquals($expected, $database->touch(2, 100));
    }

    /**
     * @return array
     */
    public function touchDataProvider()
    {
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $adapterMock->expects($this->any())
            ->method('update')
            ->will($this->returnValue(false));

        return [
            'with_store_data' => [
                'options' => $this->getOptionsWithStoreData($adapterMock),
                'expected' => false,

            ],
            'without_store_data' => [
                'options' => $this->getOptionsWithoutStoreData(),
                'expected' => true,
            ],
        ];
    }
}
