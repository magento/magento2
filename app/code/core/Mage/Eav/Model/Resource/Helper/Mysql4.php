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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Eav Mysql resource helper model
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    /**
     * Mysql column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddlColumnTypes      = array(
        Varien_Db_Ddl_Table::TYPE_BOOLEAN       => 'bool',
        Varien_Db_Ddl_Table::TYPE_SMALLINT      => 'smallint',
        Varien_Db_Ddl_Table::TYPE_INTEGER       => 'int',
        Varien_Db_Ddl_Table::TYPE_BIGINT        => 'bigint',
        Varien_Db_Ddl_Table::TYPE_FLOAT         => 'float',
        Varien_Db_Ddl_Table::TYPE_DECIMAL       => 'decimal',
        Varien_Db_Ddl_Table::TYPE_NUMERIC       => 'decimal',
        Varien_Db_Ddl_Table::TYPE_DATE          => 'date',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP     => 'timestamp',
        Varien_Db_Ddl_Table::TYPE_DATETIME      => 'datetime',
        Varien_Db_Ddl_Table::TYPE_TEXT          => 'text',
        Varien_Db_Ddl_Table::TYPE_BLOB          => 'blob',
        Varien_Db_Ddl_Table::TYPE_VARBINARY     => 'blob'
    );

    /**
     * Returns columns for select
     *
     * @param string $tableAlias
     * @param string $eavType
     * @return string|array
     */
    public function attributeSelectFields($tableAlias, $eavType)
    {
        return '*';
    }

    /**
     * Returns DDL type by column type in database
     *
     * @param string $columnType
     * @return string
     */
    public function getDdlTypeByColumnType($columnType)
    {
        switch ($columnType) {
            case 'char':
            case 'varchar':
                $columnType = 'text';
                break;
            case 'tinyint':
                $columnType = 'smallint';
                break;
        }

        return array_search($columnType, $this->_ddlColumnTypes);
    }

    /**
     * Prepares value fields for unions depend on type
     *
     * @param string $value
     * @param string $eavType
     * @return Zend_Db_Expr
     */
    public function prepareEavAttributeValue($value, $eavType)
    {
        return $value;
    }

    /**
     * Groups selects to separate unions depend on type
     *
     * @param array $selects
     * @return array
     */
    public function getLoadAttributesSelectGroups($selects)
    {
        $mainGroup  = array();
        foreach ($selects as $eavType => $selectGroup) {
            $mainGroup = array_merge($mainGroup, $selectGroup);
        }
        return array($mainGroup);
    }

    /**
     * Retrieve 'cast to int' expression
     *
     * @param string|Zend_Db_Expr $expression
     * @return Zend_Db_Expr
     */
    public function getCastToIntExpression($expression)
    {
        return new Zend_Db_Expr("CAST($expression AS SIGNED)");
    }
}
