<?php
/**
 * Abstract DB helper class
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Helper;

abstract class AbstractHelper
{
    /**
     * Resource helper module prefix
     *
     * @var string
     */
    protected $_modulePrefix;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Initialize resource helper instance
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix)
    {
        $this->_resource = $resource;
        $this->_modulePrefix = (string)$modulePrefix;
    }

    /**
     * Retrieves connection to the resource
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        return $this->_resource->getConnection($this->_modulePrefix);
    }


    /**
     * Escapes value, that participates in LIKE, with '\' symbol.
     * Note: this func cannot be used on its own, because different RDMBS may use different default escape symbols,
     * so you should either use addLikeEscape() to produce LIKE construction, or add escape symbol on your own.
     *
     * By default escapes '_', '%' and '\' symbols. If some masking symbols must not be escaped, then you can set
     * appropriate options in $options.
     *
     * $options can contain following flags:
     * - 'allow_symbol_mask' - the '_' symbol will not be escaped
     * - 'allow_string_mask' - the '%' symbol will not be escaped
     * - 'position' ('any', 'start', 'end') - expression will be formed so that $value will be found at position
     *      within string, by default when nothing set - string must be fully matched with $value
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    public function escapeLikeValue($value, $options = [])
    {
        $value = str_replace('\\', '\\\\', $value);

        $replaceFrom = [];
        $replaceTo = [];
        if (empty($options['allow_symbol_mask'])) {
            $replaceFrom[] = '_';
            $replaceTo[] = '\_';
        }
        if (empty($options['allow_string_mask'])) {
            $replaceFrom[] = '%';
            $replaceTo[] = '\%';
        }
        if ($replaceFrom) {
            $value = str_replace($replaceFrom, $replaceTo, $value);
        }

        if (isset($options['position'])) {
            switch ($options['position']) {
                case 'any':
                    $value = '%' . $value . '%';
                    break;
                case 'start':
                    $value = $value . '%';
                    break;
                case 'end':
                    $value = '%' . $value;
                    break;
                default:
                    break;
            }
        }

        return $value;
    }

    /**
     * Escapes, quotes and adds escape symbol to LIKE expression.
     * For options and escaping see escapeLikeValue().
     *
     * @param string $value
     * @param array $options
     * @return \Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    abstract public function addLikeEscape($value, $options = []);

    /**
     * Returns case insensitive LIKE construction.
     * For options and escaping see escapeLikeValue().
     *
     * @param string $field
     * @param string $value
     * @param array $options
     * @return \Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    public function getCILike($field, $value, $options = [])
    {
        $quotedField = $this->getConnection()->quoteIdentifier($field);
        return new \Zend_Db_Expr($quotedField . ' LIKE ' . $this->addLikeEscape($value, $options));
    }
}
