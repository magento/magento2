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
 * @category    Mage
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for resource setup model needed for migration process between Magento versions
 */
class Mage_Core_Model_Resource_Setup_MigrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Result of update class aliases to compare with expected.
     * Used in callback for Varien_Db_Select::update.
     *
     * @var array
     */
    protected $_actualUpdateResult;

    /**
     * Where conditions to compare with expected.
     * Used in callback for Varien_Db_Select::where.
     *
     * @var array
     */
    protected $_actualWhere;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Varien_Db_Select
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
        $autoload = $this->getMock('Magento_Autoload', array('classExists'), array(), '', false);
        $autoload->expects($this->any())
            ->method('classExists')
            ->will($this->returnCallback(array($this, 'classExistCallback')));

        $this->_selectMock = $this->getMock('Varien_Db_Select', array(), array(), '', false);
        $this->_selectMock->expects($this->any())
                    ->method('from')
                    ->will($this->returnSelf());
        $this->_selectMock->expects($this->any())
                    ->method('where')
                    ->will($this->returnCallback(array($this, 'whereCallback')));

        $adapterMock = $this->getMock('Varien_Db_Adapter_Pdo_Mysql',
            array('select', 'update', 'fetchAll', 'fetchOne'), array(), '', false
        );
        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->_selectMock));
        $adapterMock->expects($this->any())
            ->method('update')
            ->will($this->returnCallback(array($this, 'updateCallback')));
        $adapterMock->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue($tableData));
        $adapterMock->expects($this->any())
            ->method('fetchOne')
            ->will($this->returnValue($tableRowsCount));

        return array(
            'resource_config'   => 'not_used',
            'connection_config' => 'not_used',
            'module_config'     => 'not_used',
            'base_dir'          => 'not_used',
            'path_to_map_file'  => 'not_used',
            'connection'        => $adapterMock,
            'autoload'          => $autoload,
            'core_helper'       => new Mage_Core_Helper_Data(),
            'aliases_map'       => $aliasesMap
        );
    }

    /**
     * Callback for Magento_Autoload::classExist
     *
     * @return bool
     */
    public function classExistCallback()
    {
        return true;
    }

    /**
     * Callback for Varien_Db_Select::update
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
     * Callback for Varien_Db_Select::where
     *
     * @param string $condition
     * @return PHPUnit_Framework_MockObject_MockObject|Varien_Db_Select
     */
    public function whereCallback($condition)
    {
        if (null === $this->_actualWhere) {
            $this->_actualWhere = array();
        }
        if (!empty($condition) && false === strpos($condition, ' IS NOT NULL')
            && !in_array($condition, $this->_actualWhere)
        ) {
            $this->_actualWhere[] = $condition;
        }
        return $this->_selectMock;
    }

    /**
     * @covers Mage_Core_Model_Resource_Setup_Migration::appendClassAliasReplace
     */
    public function testAppendClassAliasReplace()
    {
        $setupModel = new Mage_Core_Model_Resource_Setup_Migration('core_setup', $this->_getModelDependencies());

        $setupModel->appendClassAliasReplace('tableName', 'fieldName', 'entityType', 'fieldContentType',
            array('pk_field1', 'pk_field2'), 'additionalWhere'
        );

        $expectedRulesList = array (
            'tableName' => array(
                'fieldName' => array(
                    'entity_type'      => 'entityType',
                    'content_type'     => 'fieldContentType',
                    'pk_fields'        => array('pk_field1', 'pk_field2'),
                    'additional_where' => 'additionalWhere'
                )
            )
        );

        $this->assertAttributeEquals($expectedRulesList, '_replaceRules', $setupModel);
    }

    /**
     * @dataProvider updateClassAliasesDataProvider
     * @covers Mage_Core_Model_Resource_Setup_Migration::doUpdateClassAliases
     * @covers Mage_Core_Model_Resource_Setup_Migration::_updateClassAliasesInTable
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getRowsCount
     * @covers Mage_Core_Model_Resource_Setup_Migration::_applyFieldRule
     * @covers Mage_Core_Model_Resource_Setup_Migration::_updateRowsData
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getTableData
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getReplacement
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getCorrespondingClassName
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getModelReplacement
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getPatternReplacement
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getClassName
     * @covers Mage_Core_Model_Resource_Setup_Migration::_isFactoryName
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getModuleName
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getCompositeModuleName
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getAliasFromMap
     * @covers Mage_Core_Model_Resource_Setup_Migration::_pushToMap
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getAliasesMap
     * @covers Mage_Core_Model_Resource_Setup_Migration::_getAliasInSerializedStringReplacement
     * @covers Mage_Core_Model_Resource_Setup_Migration::_parseSerializedString
     */
    public function testDoUpdateClassAliases($replaceRules, $tableData, $expected, $aliasesMap = array())
    {
        $this->_actualUpdateResult = array();

        $tableRowsCount = count($tableData);

        $setupModel = new Mage_Core_Model_Resource_Setup_Migration(
            'core_setup',
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
            'plain text replace model'         => include __DIR__ . '/_files/data_content_plain_model.php',
            'plain text replace resource'      => include __DIR__ . '/_files/data_content_plain_resource.php',
            'plain text replace with pk field' => include __DIR__ . '/_files/data_content_plain_pk_fields.php',
            'xml replace'                      => include __DIR__ . '/_files/data_content_xml.php',
            'wiki markup replace'              => include __DIR__ . '/_files/data_content_wiki.php',
            'serialized php replace'           => include __DIR__ . '/_files/data_content_serialized.php',
        );
    }

    /**
     * @covers Mage_Core_Model_Resource_Setup_Migration::getCompositeModules
     */
    public function testGetCompositeModules()
    {
        $compositeModules = Mage_Core_Model_Resource_Setup_Migration::getCompositeModules();
        $this->assertInternalType('array', $compositeModules);
        $this->assertNotEmpty($compositeModules);
        foreach ($compositeModules as $classAlias => $className) {
            $this->assertInternalType('string', $classAlias);
            $this->assertInternalType('string', $className);
            $this->assertNotEmpty($classAlias);
            $this->assertNotEmpty($className);
        }
    }
}
