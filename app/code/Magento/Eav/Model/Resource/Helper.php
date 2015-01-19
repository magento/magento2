<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource;

/**
 * Eav Mysql resource helper model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\Resource $resource, $modulePrefix = 'Magento_Eav')
    {
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Mysql column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddlColumnTypes = [
        \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN => 'bool',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT => 'smallint',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER => 'int',
        \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT => 'bigint',
        \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT => 'float',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL => 'decimal',
        \Magento\Framework\DB\Ddl\Table::TYPE_NUMERIC => 'decimal',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATE => 'date',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP => 'timestamp',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME => 'datetime',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT => 'text',
        \Magento\Framework\DB\Ddl\Table::TYPE_BLOB => 'blob',
        \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY => 'blob',
    ];

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
        $mainGroup = [];
        foreach ($selects as $selectGroup) {
            $mainGroup = array_merge($mainGroup, $selectGroup);
        }
        return [$mainGroup];
    }
}
