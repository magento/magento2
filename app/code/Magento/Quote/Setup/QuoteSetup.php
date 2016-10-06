<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 * Setup Model of Quote Module
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @codeCoverageIgnore
 */
class QuoteSetup extends EavSetup
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var string
     */
    private static $connectionName = 'checkout';

    /**
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param ScopeConfigInterface $config
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
     */
    protected function _flatTableExist($table)
    {
        $tablesList = $this->getSetup()->getConnection(self::$connectionName)->listTables();
        return in_array(
            strtoupper($this->getSetup()->getTable($table, self::$connectionName)),
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
     */
    protected function _addFlatAttribute($table, $attribute, $attr)
    {
        $tableInfo = $this->getSetup()
            ->getConnection(self::$connectionName)
            ->describeTable($this->getSetup()->getTable($table, self::$connectionName));
        if (isset($tableInfo[$attribute])) {
            return $this;
        }
        $columnDefinition = $this->_getAttributeColumnDefinition($attribute, $attr);
        $this->getSetup()->getConnection(self::$connectionName)->addColumn(
            $this->getSetup()->getTable($table, self::$connectionName),
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
     */
    public function getConfigModel()
    {
        return $this->_config;
    }

    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->_encryptor;
    }
}
