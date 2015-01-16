<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for resource setup model needed for migration process between Magento versions
 */
namespace Magento\Framework\Module\Setup;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Result of update class aliases to compare with expected.
     * Used in callback for \Magento\Framework\DB\Select::update.
     *
     * @var array
     */
    protected $_actualUpdateResult;

    /**
     * Where conditions to compare with expected.
     * Used in callback for \Magento\Framework\DB\Select::where.
     *
     * @var array
     */
    protected $_actualWhere;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Select
     */
    protected $_selectMock;

    protected function tearDown()
    {
        unset($this->_actualUpdateResult);
        unset($this->_actualWhere);
        unset($this->_selectMock);
    }

    /**
     * Retrieve all necessary objects mocks which used inside customer storage
     *
     * @param int $tableRowsCount
     * @param array $tableData
     * @param array $aliasesMap
     *
     * @return array
     */
    protected function _getModelDependencies($tableRowsCount = 0, $tableData = [], $aliasesMap = [])
    {
        $this->_selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->_selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->_selectMock->expects(
            $this->any()
        )->method(
            'where'
        )->will(
            $this->returnCallback([$this, 'whereCallback'])
        );

        $adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'update', 'fetchAll', 'fetchOne'],
            [],
            '',
            false
        );
        $adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->_selectMock));
        $adapterMock->expects(
            $this->any()
        )->method(
            'update'
        )->will(
            $this->returnCallback([$this, 'updateCallback'])
        );
        $adapterMock->expects($this->any())->method('fetchAll')->will($this->returnValue($tableData));
        $adapterMock->expects($this->any())->method('fetchOne')->will($this->returnValue($tableRowsCount));

        return [
            'resource_config' => 'not_used',
            'connection_config' => 'not_used',
            'module_config' => 'not_used',
            'base_dir' => 'not_used',
            'path_to_map_file' => 'not_used',
            'connection' => $adapterMock,
            'core_helper' => $this->getMock('Magento\Core\Helper\Data', [], [], '', false, false),
            'aliases_map' => $aliasesMap
        ];
    }

    /**
     * Callback for \Magento\Framework\DB\Select::update
     *
     * @param string $table
     * @param array $bind
     * @param array $where
     */
    public function updateCallback($table, array $bind, $where)
    {
        $fields = array_keys($bind);
        $replacements = array_values($bind);

        $this->_actualUpdateResult[] = [
            'table' => $table,
            'field' => $fields[0],
            'to' => $replacements[0],
            'from' => $where,
        ];
    }

    /**
     * Callback for \Magento\Framework\DB\Select::where
     *
     * @param string $condition
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Select
     */
    public function whereCallback($condition)
    {
        if (null === $this->_actualWhere) {
            $this->_actualWhere = [];
        }
        if (!empty($condition) && false === strpos(
            $condition,
            ' IS NOT NULL'
        ) && !in_array(
            $condition,
            $this->_actualWhere
        )
        ) {
            $this->_actualWhere[] = $condition;
        }
        return $this->_selectMock;
    }

    /**
     * @covers \Magento\Framework\Module\Setup\Migration::appendClassAliasReplace
     */
    public function testAppendClassAliasReplace()
    {
        $moduleListMock = $this->getMock('Magento\Framework\Module\ModuleListInterface');
        $moduleListMock->expects($this->once())->method('getOne')->will($this->returnValue([]));

        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $modulesDirMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($modulesDirMock));

        $contextMock = $this->getMock('Magento\Framework\Module\Setup\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystemMock));
        $contextMock->expects($this->once())
            ->method('getEventManager')
            ->will(
                $this->returnValue(
                    $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false)
                )
            );
        $contextMock->expects($this->once())
            ->method('getResourceModel')
            ->will($this->returnValue($this->getMock('Magento\Framework\App\Resource', [], [], '', false)));
        $contextMock->expects($this->once())
            ->method('getLogger')
            ->will($this->returnValue($this->getMock('Psr\Log\LoggerInterface')));
        $contextMock->expects($this->once())
            ->method('getModulesReader')
            ->will(
                $this->returnValue(
                    $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false)
                )
            );
        $contextMock->expects($this->once())->method('getModuleList')->will($this->returnValue($moduleListMock));

        $migrationData = $this->getMock('Magento\Framework\Module\Setup\MigrationData', [], [], '', false);

        $setupModel = new \Magento\Framework\Module\Setup\Migration(
            $contextMock,
            'core_setup',
            'Magento_Core',
            $migrationData,
            'app/etc/aliases_to_classes_map.json'
        );

        $setupModel->appendClassAliasReplace(
            'tableName',
            'fieldName',
            'entityType',
            'fieldContentType',
            ['pk_field1', 'pk_field2'],
            'additionalWhere'
        );

        $expectedRulesList = [
            'tableName' => [
                'fieldName' => [
                    'entity_type' => 'entityType',
                    'content_type' => 'fieldContentType',
                    'pk_fields' => ['pk_field1', 'pk_field2'],
                    'additional_where' => 'additionalWhere',
                ],
            ],
        ];

        $this->assertAttributeEquals($expectedRulesList, '_replaceRules', $setupModel);
    }

    /**
     * @dataProvider updateClassAliasesDataProvider
     */
    public function testDoUpdateClassAliases($replaceRules, $tableData, $expected, $aliasesMap = [])
    {
        $this->markTestIncomplete('Requires refactoring of class that is tested, covers to many methods');

        $this->_actualUpdateResult = [];
        $tableRowsCount = count($tableData);

        $setupModel = new \Magento\Framework\Module\Setup\Migration(
            $this->getMock('Magento\Framework\App\Resource', [], [], '', false, false),
            $this->getMock('Magento\Framework\Filesystem', [], [], '', false),
            $this->getMock('Magento\Core\Helper\Data', [], [], '', false),
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
            $this->getMock('Magento\Framework\Module\ModuleListInterface'),
            $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false, false),
            $this->getMock('Magento\Framework\Module\Resource', [], [], '', false),
            $this->getMock('Magento\Core\Model\Resource\Theme\CollectionFactory', [], [], '', false),
            $this->getMock('Magento\Core\Model\Theme\CollectionFactory', [], [], '', false),
            $this->getMock('Magento\Framework\Module\Setup\MigrationFactory', [], [], '', false),
            'core_setup',
            'app/etc/aliases_to_classes_map.json',
            $this->_getModelDependencies($tableRowsCount, $tableData, $aliasesMap)
        );

        $setupModel->setTable('table', 'table');

        foreach ($replaceRules as $replaceRule) {
            call_user_func_array([$setupModel, 'appendClassAliasReplace'], $replaceRule);
        }

        $setupModel->doUpdateClassAliases();

        $this->assertEquals($expected['updates'], $this->_actualUpdateResult);

        if (isset($expected['where'])) {
            $this->assertEquals($expected['where'], $this->_actualWhere);
        }

        if (isset($expected['aliases_map'])) {
            $this->assertAttributeEquals($expected['aliases_map'], '_aliasesMap', $setupModel);
        }
    }

    /**
     * Data provider for updating class aliases
     *
     * @return array
     */
    public function updateClassAliasesDataProvider()
    {
        return [
            'plain text replace model' => include __DIR__ . '/_files/data_content_plain_model.php',
            'plain text replace resource' => include __DIR__ . '/_files/data_content_plain_resource.php',
            'plain text replace with pk field' => include __DIR__ . '/_files/data_content_plain_pk_fields.php',
            'xml replace' => include __DIR__ . '/_files/data_content_xml.php',
            'wiki markup replace' => include __DIR__ . '/_files/data_content_wiki.php',
            'serialized php replace' => include __DIR__ . '/_files/data_content_serialized.php'
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected function _getFilesystemMock()
    {
        $mock = $this->getMockBuilder('Magento\Framework\Filesystem')->disableOriginalConstructor()->getMock();
        return $mock;
    }
}
