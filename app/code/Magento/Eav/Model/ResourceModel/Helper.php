<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

/**
 * Eav Mysql resource helper model
 *
 * @api
 * @since 100.0.2
 */
class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Construct
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     * @codeCoverageIgnore
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix = 'Magento_Eav')
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
     * Attribute types that can be united via UNION into one query
     * while selecting attribute`s data from tables like `catalog_product_entity_datatype`
     *
     * This helps to run one query with all data types instead of one per each data type
     *
     * This data types are determined as 'groupable' because their tables have the same structure
     * which means that they can be used in one UNION query
     *
     * @var array
     */
    private $_groupableTypes = ['varchar', 'text', 'decimal', 'datetime', 'int'];

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
     * E.g. for input array:
     *  [
     *      varchar => [select1, select2],
     *      text    => [select3],
     *      int     => [select4],
     *      bool    => [select5]
     *  ]
     *
     * The result array will be:
     *  [
     *      0 => [select1, select2, select3, select4] // contains queries for varchar & text & int
     *      1 => [select5]                            // contains queries for bool
     *  ]
     *
     * @param array $selects
     * @return array
     */
    public function getLoadAttributesSelectGroups($selects)
    {
        $mainGroup = [];

        foreach ($selects as $dataType => $selectGroup) {
            if (in_array($dataType, $this->_groupableTypes)) {
                $mainGroup['all'][] = $selectGroup;
                continue;
            }

            $mainGroup[$dataType] = $selectGroup;
        }

        if (array_key_exists('all', $mainGroup)) {
            // it is better to call array_merge once after loop instead of calling it on each loop
            $mainGroup['all'] = array_merge([], ...$mainGroup['all']);
        }

        return array_values($mainGroup);
    }
}
