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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for resource setup model needed for migration process between Magento versions
 */
namespace Magento\Core\Model\Resource\Setup;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Result of update class aliases to compare with expected.
     * Used in callback for \Magento\DB\Select::update.
     *
     * @var array
     */
    protected $_actualUpdateResult;

    /**
     * Where conditions to compare with expected.
     * Used in callback for \Magento\DB\Select::where.
     *
     * @var array
     */
    protected $_actualWhere;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\DB\Select
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
        $this->_selectMock = $this->getMock('Magento\DB\Select', array(), array(), '', false);
        $this->_selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->_selectMock->expects(
            $this->any()
        )->method(
            'where'
        )->will(
            $this->returnCallback(array($this, 'whereCallback'))
        );

        $adapterMock = $this->getMock(
            'Magento\DB\Adapter\Pdo\Mysql',
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
     * Callback for \Magento\DB\Select::update
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
     * Callback for \Magento\DB\Select::where
     *
     * @param string $condition
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\DB\Select
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
     * @covers \Magento\Core\Model\Resource\Setup\Migration::appendClassAliasReplace
     */
    public function testAppendClassAliasReplace()
    {
        $moduleListMock = $this->getMock('Magento\Module\ModuleListInterface');
        $moduleListMock->expects($this->once())->method('getModule')->will($this->returnValue(array()));

        $contextMock = $this->getMock('Magento\Core\Model\Resource\Setup\Context', array(), array(), '', false);
        $filesystemMock = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $contextMock->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystemMock));
        $modulesDirMock = $this->getMock('Magento\Filesystem\Directory\Read', array(), array(), '', false);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($modulesDirMock));

        $contextMock->expects(
            $this->once()
        )->method(
            'getEventManager'
        )->will(
            $this->returnValue($this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false))
        );
        $contextMock->expects(
            $this->once()
        )->method(
            'getResourceModel'
        )->will(
            $this->returnValue($this->getMock('Magento\App\Resource', array(), array(), '', false))
        );
        $contextMock->expects(
            $this->once()
        )->method(
            'getLogger'
        )->will(
            $this->returnValue($this->getMock('Magento\Logger', array(), array(), '', false))
        );
        $contextMock->expects(
            $this->once()
        )->method(
            'getModulesReader'
        )->will(
            $this->returnValue($this->getMock('Magento\Module\Dir\Reader', array(), array(), '', false))
        );
        $contextMock->expects($this->once())->method('getModuleList')->will($this->returnValue($moduleListMock));

        $setupModel = new \Magento\Core\Model\Resource\Setup\Migration(
            $contextMock,
            'core_setup',
            $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false),
            $this->getMock('Magento\App\Filesystem', array(), array(), '', false),
            ''
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
     * @covers \Magento\Core\Model\Resource\Setup\Migration::doUpdateClassAliases
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_updateClassAliasesInTable
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getRowsCount
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_applyFieldRule
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_updateRowsData
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getTableData
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getReplacement
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getCorrespondingClassName
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getModelReplacement
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getPatternReplacement
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getClassName
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_isFactoryName
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getModuleName
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getCompositeModuleName
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getAliasFromMap
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_pushToMap
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getAliasesMap
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_getAliasInSerializedStringReplacement
     * @covers \Magento\Core\Model\Resource\Setup\Migration::_parseSerializedString
     */
    public function testDoUpdateClassAliases($replaceRules, $tableData, $expected, $aliasesMap = array())
    {
        $this->markTestIncomplete('Requires refactoring of class that is tested, covers to many methods');

        $this->_actualUpdateResult = array();
        $tableRowsCount = count($tableData);

        $setupModel = new \Magento\Core\Model\Resource\Setup\Migration(
            $this->getMock('Magento\App\Resource', array(), array(), '', false, false),
            $this->getMock('Magento\App\Filesystem', array(), array(), '', false),
            $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false),
            $this->getMock('Magento\Logger', array(), array(), '', false),
            $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false),
            $this->getMock('Magento\App\ConfigInterface', array(), array(), '', false, false),
            $this->getMock('Magento\Module\ModuleListInterface'),
            $this->getMock('Magento\Module\Dir\Reader', array(), array(), '', false, false),
            $this->getMock('Magento\Core\Model\Resource\Resource', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Resource\Theme\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Theme\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\Resource\Setup\MigrationFactory', array(), array(), '', false),
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
     * @covers \Magento\Core\Model\Resource\Setup\Migration::getCompositeModules
     */
    public function testGetCompositeModules()
    {
        $compositeModules = \Magento\Core\Model\Resource\Setup\Migration::getCompositeModules();
        $this->assertInternalType('array', $compositeModules);
        $this->assertNotEmpty($compositeModules);
        foreach ($compositeModules as $classAlias => $className) {
            $this->assertInternalType('string', $classAlias);
            $this->assertInternalType('string', $className);
            $this->assertNotEmpty($classAlias);
            $this->assertNotEmpty($className);
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\App\Filesystem
     */
    protected function _getFilesystemMock()
    {
        $mock = $this->getMockBuilder('Magento\App\Filesystem')->disableOriginalConstructor()->getMock();
        return $mock;
    }
}
