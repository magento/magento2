<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected function _getModelDependencies($tableRowsCount = 0, $tableData = array(), $aliasesMap = array())
    {
        $this->_selectMock = $this->getMock('Magento\Framework\DB\Select', array(), array(), '', false);
        $this->_selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->_selectMock->expects(
            $this->any()
        )->method(
            'where'
        )->will(
            $this->returnCallback(array($this, 'whereCallback'))
        );

        $adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('select', 'update', 'fetchAll', 'fetchOne'),
            array(),
            '',
            false
        );
        $adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->_selectMock));
        $adapterMock->expects(
            $this->any()
        )->method(
            'update'
        )->will(
            $this->returnCallback(array($this, 'updateCallback'))
        );
        $adapterMock->expects($this->any())->method('fetchAll')->will($this->returnValue($tableData));
        $adapterMock->expects($this->any())->method('fetchOne')->will($this->returnValue($tableRowsCount));

        return array(
            'resource_config' => 'not_used',
            'connection_config' => 'not_used',
            'module_config' => 'not_used',
            'base_dir' => 'not_used',
            'path_to_map_file' => 'not_used',
            'connection' => $adapterMock,
            'core_helper' => $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false, false),
            'aliases_map' => $aliasesMap
        );
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

        $this->_actualUpdateResult[] = array(
            'table' => $table,
            'field' => $fields[0],
            'to' => $replacements[0],
            'from' => $where
        );
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
            $this->_actualWhere = array();
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
        $moduleListMock->expects($this->once())->method('getModule')->will($this->returnValue(array()));

        $filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $modulesDirMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($modulesDirMock));

        $contextMock = $this->getMock('Magento\Framework\Module\Setup\Context', array(), array(), '', false);
        $contextMock->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystemMock));
        $contextMock->expects($this->once())
            ->method('getEventManager')
            ->will(
                $this->returnValue(
                    $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false)
                )
            );
        $contextMock->expects($this->once())
            ->method('getResourceModel')
            ->will($this->returnValue($this->getMock('Magento\Framework\App\Resource', array(), array(), '', false)));
        $contextMock->expects($this->once())
            ->method('getLogger')
            ->will($this->returnValue($this->getMock('Magento\Framework\Logger', array(), array(), '', false)));
        $contextMock->expects($this->once())
            ->method('getModulesReader')
            ->will(
                $this->returnValue(
                    $this->getMock('Magento\Framework\Module\Dir\Reader', array(), array(), '', false)
                )
            );
        $contextMock->expects($this->once())->method('getModuleList')->will($this->returnValue($moduleListMock));

        $migrationData = $this->getMock('Magento\Framework\Module\Setup\MigrationData', array(), array(), '', false);

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
            array('pk_field1', 'pk_field2'),
            'additionalWhere'
        );

        $expectedRulesList = array(
            'tableName' => array(
                'fieldName' => array(
                    'entity_type' => 'entityType',
                    'content_type' => 'fieldContentType',
                    'pk_fields' => array('pk_field1', 'pk_field2'),
                    'additional_where' => 'additionalWhere'
                )
            )
        );

        $this->assertAttributeEquals($expectedRulesList, '_replaceRules', $setupModel);
    }

    /**
     * @dataProvider updateClassAliasesDataProvider
     * @covers \Magento\Framework\Module\Setup\Migration::doUpdateClassAliases
     * @covers \Magento\Framework\Module\Setup\Migration::_updateClassAliasesInTable
     * @covers \Magento\Framework\Module\Setup\Migration::_getRowsCount
     * @covers \Magento\Framework\Module\Setup\Migration::_applyFieldRule
     * @covers \Magento\Framework\Module\Setup\Migration::_updateRowsData
     * @covers \Magento\Framework\Module\Setup\Migration::_getTableData
     * @covers \Magento\Framework\Module\Setup\Migration::_getReplacement
     * @covers \Magento\Framework\Module\Setup\Migration::_getCorrespondingClassName
     * @covers \Magento\Framework\Module\Setup\Migration::_getModelReplacement
     * @covers \Magento\Framework\Module\Setup\Migration::_getPatternReplacement
     * @covers \Magento\Framework\Module\Setup\Migration::_getClassName
     * @covers \Magento\Framework\Module\Setup\Migration::_isFactoryName
     * @covers \Magento\Framework\Module\Setup\Migration::_getModuleName
     * @covers \Magento\Framework\Module\Setup\Migration::_getCompositeModuleName
     * @covers \Magento\Framework\Module\Setup\Migration::_getAliasFromMap
     * @covers \Magento\Framework\Module\Setup\Migration::_pushToMap
     * @covers \Magento\Framework\Module\Setup\Migration::_getAliasesMap
     * @covers \Magento\Framework\Module\Setup\Migration::_getAliasInSerializedStringReplacement
     * @covers \Magento\Framework\Module\Setup\Migration::_parseSerializedString
     */
    public function testDoUpdateClassAliases($replaceRules, $tableData, $expected, $aliasesMap = array())
    {
        $this->markTestIncomplete('Requires refactoring of class that is tested, covers to many methods');

        $this->_actualUpdateResult = array();
        $tableRowsCount = count($tableData);

        $setupModel = new \Magento\Framework\Module\Setup\Migration(
            $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false, false),
            $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false),
            $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false),
            $this->getMock('Magento\Framework\Logger', array(), array(), '', false),
            $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false),
            $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
            $this->getMock('Magento\Framework\Module\ModuleListInterface'),
            $this->getMock('Magento\Framework\Module\Dir\Reader', array(), array(), '', false, false),
            $this->getMock('Magento\Install\Model\Resource\Resource', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Resource\Theme\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Theme\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Framework\Module\Setup\MigrationFactory', array(), array(), '', false),
            'core_setup',
            'app/etc/aliases_to_classes_map.json',
            $this->_getModelDependencies($tableRowsCount, $tableData, $aliasesMap)
        );

        $setupModel->setTable('table', 'table');

        foreach ($replaceRules as $replaceRule) {
            call_user_func_array(array($setupModel, 'appendClassAliasReplace'), $replaceRule);
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
        return array(
            'plain text replace model' => include __DIR__ . '/_files/data_content_plain_model.php',
            'plain text replace resource' => include __DIR__ . '/_files/data_content_plain_resource.php',
            'plain text replace with pk field' => include __DIR__ . '/_files/data_content_plain_pk_fields.php',
            'xml replace' => include __DIR__ . '/_files/data_content_xml.php',
            'wiki markup replace' => include __DIR__ . '/_files/data_content_wiki.php',
            'serialized php replace' => include __DIR__ . '/_files/data_content_serialized.php'
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem
     */
    protected function _getFilesystemMock()
    {
        $mock = $this->getMockBuilder('Magento\Framework\App\Filesystem')->disableOriginalConstructor()->getMock();
        return $mock;
    }
}
