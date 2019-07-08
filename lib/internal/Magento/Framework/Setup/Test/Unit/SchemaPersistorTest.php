<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\SchemaListener;
use Magento\Framework\Setup\XmlPersistor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for schema persistor.
 *
 * @package Magento\Framework\Setup\Test\Unit
 */
class SchemaPersistorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Setup\SchemaPersistor
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var XmlPersistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $xmlPersistor;

    protected function setUp() : void
    {
        $this->componentRegistrarMock = $this->getMockBuilder(ComponentRegistrar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->xmlPersistor = $this->getMockBuilder(XmlPersistor::class)
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Setup\SchemaPersistor::class,
            [
                'componentRegistrar' => $this->componentRegistrarMock,
                'xmlPersistor' => $this->xmlPersistor
            ]
        );
    }

    /**
     * @dataProvider schemaListenerTablesDataProvider
     * @param array $tables
     * @param string $expectedXML
     */
    public function testPersist(array $tables, $expectedXML) : void
    {
        $moduleName = 'First_Module';
        /** @var SchemaListener|\PHPUnit_Framework_MockObject_MockObject $schemaListenerMock */
        $schemaListenerMock = $this->getMockBuilder(SchemaListener::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaListenerMock->expects(self::once())
            ->method('getTables')
            ->willReturn($tables);
        $this->componentRegistrarMock->expects(self::once())
            ->method('getPath')
            ->with('module', $moduleName)
            ->willReturn('some-non-existing-path');
        $simpleXmlElement = new \SimpleXMLElement($expectedXML);
        $this->xmlPersistor
            ->expects(self::once())
            ->method('persist')
            ->with($simpleXmlElement, 'some-non-existing-path/etc/db_schema.xml');

        $this->model->persist($schemaListenerMock);
    }

    /**
     * Provide listened schema.
     *
     * @return array
     */
    public function schemaListenerTablesDataProvider() : array
    {
        return [
            [
                'schemaListenerTables' => [
                    'First_Module' => [
                        'first_table' => [
                            'disabled' => false,
                            'name' => 'first_table',
                            'resource' => 'default',
                            'engine' => 'innodb',
                            'columns' => [
                                'first_column' => [
                                    'name' => 'first_column',
                                    'xsi:type' => 'integer',
                                    'nullable' => 1,
                                    'unsigned' => '0',
                                ],
                                'second_column' => [
                                    'name' => 'second_column',
                                    'xsi:type' => 'date',
                                    'nullable' => 0,
                                ]
                            ],
                            'indexes' => [
                                'TEST_INDEX' => [
                                    'name' => 'TEST_INDEX',
                                    'indexType' => 'btree',
                                    'columns' => [
                                        'first_column'
                                    ]
                                ]
                            ],
                            'constraints' => [
                                'foreign' => [
                                    'some_foreign_constraint' => [
                                        'referenceTable' => 'table',
                                        'referenceColumn' => 'column',
                                        'table' => 'first_table',
                                        'column' => 'first_column'
                                    ]
                                ],
                                'primary' => [
                                    'PRIMARY' => [
                                        'xsi:type' => 'primary',
                                        'name' => 'PRIMARY',
                                        'columns' => [
                                            'second_column'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                // @codingStandardsIgnoreStart
                'XMLResult' => '<?xml version="1.0"?>
                        <schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                            xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd">
                            <table name="first_table" resource="default" engine="innodb">
                                <column xmlns:xsi="xsi" xsi:type="integer" name="first_column" nullable="1" 
                                    unsigned="0"/>
                                <column xmlns:xsi="xsi" xsi:type="date" name="second_column" nullable="0"/>
                                <constraint xmlns:xsi="xsi" xsi:type="foreign" referenceId="some_foreign_constraint" 
                                    referenceTable="table" referenceColumn="column" 
                                    table="first_table" column="first_column"/>
                                <constraint xmlns:xsi="xsi" xsi:type="primary" referenceId="PRIMARY">
                                    <column name="second_column"/>
                                </constraint>
                                <index referenceId="TEST_INDEX" indexType="btree">
                                    <column name="first_column"/>
                                </index>
                            </table>
                        </schema>'
                // @codingStandardsIgnoreEnd
            ]
        ];
    }
}
