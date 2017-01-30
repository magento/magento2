<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Setup Model of Sales Module
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesSetup extends \Magento\Eav\Setup\EavSetup
{
    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var string
     */
    private static $connectionName = 'sales';

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
        $this->config = $config;
        $this->encryptor = $context->getEncryptor();
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * List of entities converted from EAV to flat data structure
     *
     * @var $_flatEntityTables array
     */
    protected $_flatEntityTables = [
        'order' => 'sales_order',
        'order_payment' => 'sales_order_payment',
        'order_item' => 'sales_order_item',
        'order_address' => 'sales_order_address',
        'order_status_history' => 'sales_order_status_history',
        'invoice' => 'sales_invoice',
        'invoice_item' => 'sales_invoice_item',
        'invoice_comment' => 'sales_invoice_comment',
        'creditmemo' => 'sales_creditmemo',
        'creditmemo_item' => 'sales_creditmemo_item',
        'creditmemo_comment' => 'sales_creditmemo_comment',
        'shipment' => 'sales_shipment',
        'shipment_item' => 'sales_shipment_item',
        'shipment_track' => 'sales_shipment_track',
        'shipment_comment' => 'sales_shipment_comment',
    ];

    /**
     * List of entities used with separate grid table
     *
     * @var string[] $_flatEntitiesGrid
     */
    protected $_flatEntitiesGrid = ['order', 'invoice', 'shipment', 'creditmemo'];

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
            $this->_addGridAttribute($this->_flatEntityTables[$entityTypeId], $code, $attr, $entityTypeId);
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
     * Add attribute to grid table if necessary
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @param string $entityTypeId
     * @return $this
     */
    protected function _addGridAttribute($table, $attribute, $attr, $entityTypeId)
    {
        if (in_array($entityTypeId, $this->_flatEntitiesGrid) && !empty($attr['grid'])) {
            $columnDefinition = $this->_getAttributeColumnDefinition($attribute, $attr);
            $this->getSetup()->getConnection(self::$connectionName)->addColumn(
                $this->getSetup()->getTable($table . '_grid', self::$connectionName),
                $attribute,
                $columnDefinition
            );
        }
        return $this;
    }

    /**
     * Retrieve definition of column for create in flat table
     *
     * @param string $code
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = [
            'order' => [
                'entity_type_id' => 5,
                'entity_model' => 'Magento\Sales\Model\ResourceModel\Order',
                'table' => 'sales_order',
                'increment_model' => 'Magento\Eav\Model\Entity\Increment\NumericValue',
                'increment_per_store' => true,
                'attributes' => [],
            ],
            'invoice' => [
                'entity_type_id' => 6,
                'entity_model' => 'Magento\Sales\Model\ResourceModel\Order\Invoice',
                'table' => 'sales_invoice',
                'increment_model' => 'Magento\Eav\Model\Entity\Increment\NumericValue',
                'increment_per_store' => true,
                'attributes' => [],
            ],
            'creditmemo' => [
                'entity_type_id' => 7,
                'entity_model' => 'Magento\Sales\Model\ResourceModel\Order\Creditmemo',
                'table' => 'sales_creditmemo',
                'increment_model' => 'Magento\Eav\Model\Entity\Increment\NumericValue',
                'increment_per_store' => true,
                'attributes' => [],
            ],
            'shipment' => [
                'entity_type_id' => 8,
                'entity_model' => 'Magento\Sales\Model\ResourceModel\Order\Shipment',
                'table' => 'sales_shipment',
                'increment_model' => 'Magento\Eav\Model\Entity\Increment\NumericValue',
                'increment_per_store' => true,
                'attributes' => [],
            ],
        ];
        return $entities;
    }

    /**
     * Get config model
     *
     * @return ScopeConfigInterface
     */
    public function getConfigModel()
    {
        return $this->config;
    }

    /**
     * @return EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }
}
