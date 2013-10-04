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
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Eav Mysql resource helper model
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Resource;

class Helper extends \Magento\Core\Model\Resource\Helper
{
    /**
     * Construct
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Core\Model\Resource $resource, $modulePrefix = 'Magento_Eav')
    {
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Mysql column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddlColumnTypes = array(
        \Magento\DB\Ddl\Table::TYPE_BOOLEAN       => 'bool',
        \Magento\DB\Ddl\Table::TYPE_SMALLINT      => 'smallint',
        \Magento\DB\Ddl\Table::TYPE_INTEGER       => 'int',
        \Magento\DB\Ddl\Table::TYPE_BIGINT        => 'bigint',
        \Magento\DB\Ddl\Table::TYPE_FLOAT         => 'float',
        \Magento\DB\Ddl\Table::TYPE_DECIMAL       => 'decimal',
        \Magento\DB\Ddl\Table::TYPE_NUMERIC       => 'decimal',
        \Magento\DB\Ddl\Table::TYPE_DATE          => 'date',
        \Magento\DB\Ddl\Table::TYPE_TIMESTAMP     => 'timestamp',
        \Magento\DB\Ddl\Table::TYPE_DATETIME      => 'datetime',
        \Magento\DB\Ddl\Table::TYPE_TEXT          => 'text',
        \Magento\DB\Ddl\Table::TYPE_BLOB          => 'blob',
        \Magento\DB\Ddl\Table::TYPE_VARBINARY     => 'blob'
    );

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
            default:
                break;
        }

        return array_search($columnType, $this->_ddlColumnTypes);
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
        foreach ($selects as $selectGroup) {
            $mainGroup = array_merge($mainGroup, $selectGroup);
        }
        return array($mainGroup);
    }
}
