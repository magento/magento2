<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Quote module setup class
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class QuoteSetup extends EavSetup
{
    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $_encryptor;

    /**
     * @var string
     * @since 2.2.0
     */
    private static $connectionName = 'checkout';

    /**
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param ScopeConfigInterface $config
     * @since 2.0.0
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        ScopeConfigInterface $config
    ) {
        $this->_config = $config;
        $this->_encryptor = $context->getEncryptor();
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * List of entities converted from EAV to flat data structure
     *
     * @var $_flatEntityTables array
     * @since 2.0.0
     */
    protected $_flatEntityTables = [
        'quote' => 'quote',
        'quote_item' => 'quote_item',
        'quote_address' => 'quote_address',
        'quote_address_item' => 'quote_address_item',
        'quote_address_rate' => 'quote_shipping_rate',
        'quote_payment' => 'quote_payment',
    ];

    /**
     * Check if table exist for flat entity
     *
     * @param string $table
     * @return bool
     * @since 2.0.0
     */
    protected function _flatTableExist($table)
    {
        $tablesList = $this->getConnection()->listTables();
        return in_array(
            strtoupper($this->getTable($table)),
            array_map('strtoupper', $tablesList)
        );
    }

    /**
     * Add entity attribute. Overwritten for flat entities support
     *
     * @param int|string $entityTypeId
     * @param string $code
     * @param array $attr
     * @return $this
     * @since 2.0.0
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        if (isset(
            $this->_flatEntityTables[$entityTypeId]
        ) && $this->_flatTableExist(
            $this->_flatEntityTables[$entityTypeId]
        )
        ) {
            $this->_addFlatAttribute($this->_flatEntityTables[$entityTypeId], $code, $attr);
        } else {
            parent::addAttribute($entityTypeId, $code, $attr);
        }
        return $this;
    }

    /**
     * Add attribute as separate column in the table
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @return $this
     * @since 2.0.0
     */
    protected function _addFlatAttribute($table, $attribute, $attr)
    {
        $tableInfo = $this->getConnection()
            ->describeTable($this->getTable($table));
        if (isset($tableInfo[$attribute])) {
            return $this;
        }
        $columnDefinition = $this->_getAttributeColumnDefinition($attribute, $attr);
        $this->getConnection()->addColumn(
            $this->getTable($table),
            $attribute,
            $columnDefinition
        );
        return $this;
    }

    /**
     * Retrieve definition of column for create in flat table
     *
     * @param string $code
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function _getAttributeColumnDefinition($code, $data)
    {
        // Convert attribute type to column info
        $data['type'] = isset($data['type']) ? $data['type'] : 'varchar';
        $type = null;
        $length = null;
        switch ($data['type']) {
            case 'timestamp':
                $type = \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP;
                break;
            case 'datetime':
                $type = \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME;
                break;
            case 'decimal':
                $type = \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL;
                $length = '12,4';
                break;
            case 'int':
                $type = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
                break;
            case 'text':
                $type = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
                $length = 65536;
                break;
            case 'char':
            case 'varchar':
                $type = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
                $length = 255;
                break;
        }
        if ($type !== null) {
            $data['type'] = $type;
            $data['length'] = $length;
        }

        $data['nullable'] = isset($data['required']) ? !$data['required'] : true;
        $data['comment'] = isset($data['comment']) ? $data['comment'] : ucwords(str_replace('_', ' ', $code));
        return $data;
    }

    /**
     * Get config model
     *
     * @return ScopeConfigInterface
     * @since 2.0.0
     */
    public function getConfigModel()
    {
        return $this->_config;
    }

    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    public function getEncryptor()
    {
        return $this->_encryptor;
    }

    /**
     * Get quote connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.2.0
     */
    public function getConnection()
    {
        return $this->getSetup()->getConnection(self::$connectionName);
    }

    /**
     * Get table name
     *
     * @param string $table
     * @return string
     * @since 2.2.0
     */
    public function getTable($table)
    {
        return $this->getSetup()->getTable($table, self::$connectionName);
    }
}
