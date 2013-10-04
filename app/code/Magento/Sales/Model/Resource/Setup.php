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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Setup Model of Sales Module
 */
namespace Magento\Sales\Model\Resource;

class Setup extends \Magento\Eav\Model\Entity\Setup
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\Core\Model\Resource\Setup\Context $context
     * @param \Magento\Core\Model\CacheInterface $cache
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGrCollFactory
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Core\Model\Config $config
     * @param string $resourceName
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Core\Model\Resource\Setup\Context $context,
        \Magento\Core\Model\CacheInterface $cache,
        \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGrCollFactory,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Core\Model\Config $config,
        $resourceName,
        $moduleName = 'Magento_Sales',
        $connectionName = ''
    ) {
        $this->_config = $config;
        $this->_coreData = $coreHelper;
        parent::__construct($context, $cache, $attrGrCollFactory, $resourceName, $moduleName, $connectionName);
    }

    /**
     * List of entities converted from EAV to flat data structure
     *
     * @var $_flatEntityTables array
     */
    protected $_flatEntityTables     = array(
        'quote'             => 'sales_flat_quote',
        'quote_item'        => 'sales_flat_quote_item',
        'quote_address'     => 'sales_flat_quote_address',
        'quote_address_item'=> 'sales_flat_quote_address_item',
        'quote_address_rate'=> 'sales_flat_quote_shipping_rate',
        'quote_payment'     => 'sales_flat_quote_payment',
        'order'             => 'sales_flat_order',
        'order_payment'     => 'sales_flat_order_payment',
        'order_item'        => 'sales_flat_order_item',
        'order_address'     => 'sales_flat_order_address',
        'order_status_history' => 'sales_flat_order_status_history',
        'invoice'           => 'sales_flat_invoice',
        'invoice_item'      => 'sales_flat_invoice_item',
        'invoice_comment'   => 'sales_flat_invoice_comment',
        'creditmemo'        => 'sales_flat_creditmemo',
        'creditmemo_item'   => 'sales_flat_creditmemo_item',
        'creditmemo_comment'=> 'sales_flat_creditmemo_comment',
        'shipment'          => 'sales_flat_shipment',
        'shipment_item'     => 'sales_flat_shipment_item',
        'shipment_track'    => 'sales_flat_shipment_track',
        'shipment_comment'  => 'sales_flat_shipment_comment',
    );

    /**
     * List of entities used with separate grid table
     *
     * @var $_flatEntitiesGrid array
     */
    protected $_flatEntitiesGrid     = array(
        'order',
        'invoice',
        'shipment',
        'creditmemo'
    );

    /**
     * Check if table exist for flat entity
     *
     * @param string $table
     * @return bool
     */
    protected function _flatTableExist($table)
    {
        $tablesList = $this->getConnection()->listTables();
        return in_array(strtoupper($this->getTable($table)), array_map('strtoupper', $tablesList));
    }

    /**
     * Add entity attribute. Overwrited for flat entities support
     *
     * @param int|string $entityTypeId
     * @param string $code
     * @param array $attr
     * @return \Magento\Sales\Model\Resource\Setup
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        if (isset($this->_flatEntityTables[$entityTypeId]) &&
            $this->_flatTableExist($this->_flatEntityTables[$entityTypeId]))
        {
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
     * @return \Magento\Sales\Model\Resource\Setup
     */
    protected function _addFlatAttribute($table, $attribute, $attr)
    {
        $tableInfo = $this->getConnection()->describeTable($this->getTable($table));
        if (isset($tableInfo[$attribute])) {
            return $this;
        }
        $columnDefinition = $this->_getAttributeColumnDefinition($attribute, $attr);
        $this->getConnection()->addColumn($this->getTable($table), $attribute, $columnDefinition);
        return $this;
    }

    /**
     * Add attribute to grid table if necessary
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @param string $entityTypeId
     * @return \Magento\Sales\Model\Resource\Setup
     */
    protected function _addGridAttribute($table, $attribute, $attr, $entityTypeId)
    {
        if (in_array($entityTypeId, $this->_flatEntitiesGrid) && !empty($attr['grid'])) {
            $columnDefinition = $this->_getAttributeColumnDefinition($attribute, $attr);
            $this->getConnection()->addColumn($this->getTable($table . '_grid'), $attribute, $columnDefinition);
        }
        return $this;
    }

    /**
     * Retrieve definition of column for create in flat table
     *
     * @param string $code
     * @param array $data
     * @return array
     */
    protected function _getAttributeColumnDefinition($code, $data)
    {
        // Convert attribute type to column info
        $data['type'] = isset($data['type']) ? $data['type'] : 'varchar';
        $type = null;
        $length = null;
        switch ($data['type']) {
            case 'timestamp':
                $type = \Magento\DB\Ddl\Table::TYPE_TIMESTAMP;
                break;
            case 'datetime':
                $type = \Magento\DB\Ddl\Table::TYPE_DATETIME;
                break;
            case 'decimal':
                $type = \Magento\DB\Ddl\Table::TYPE_DECIMAL;
                $length = '12,4';
                break;
            case 'int':
                $type = \Magento\DB\Ddl\Table::TYPE_INTEGER;
                break;
            case 'text':
                $type = \Magento\DB\Ddl\Table::TYPE_TEXT;
                $length = 65536;
                break;
            case 'char':
            case 'varchar':
                $type = \Magento\DB\Ddl\Table::TYPE_TEXT;
                $length = 255;
                break;
        }
        if ($type !== null) {
            $data['type'] = $type;
            $data['length'] = $length;
        }

        $data['nullable'] = isset($data['required']) ? !$data['required'] : true;
        $data['comment']  = isset($data['comment']) ? $data['comment'] : ucwords(str_replace('_', ' ', $code));
        return $data;
    }

    public function getDefaultEntities()
    {
        $entities = array(
            'order'                       => array(
                'entity_model'                   => 'Magento\Sales\Model\Resource\Order',
                'table'                          => 'sales_flat_order',
                'increment_model'                => 'Magento\Eav\Model\Entity\Increment\Numeric',
                'increment_per_store'            => true,
                'attributes'                     => array()
            ),
            'invoice'                       => array(
                'entity_model'                   => 'Magento\Sales\Model\Resource\Order\Invoice',
                'table'                          => 'sales_flat_invoice',
                'increment_model'                => 'Magento\Eav\Model\Entity\Increment\Numeric',
                'increment_per_store'            => true,
                'attributes'                     => array()
            ),
            'creditmemo'                       => array(
                'entity_model'                   => 'Magento\Sales\Model\Resource\Order\Creditmemo',
                'table'                          => 'sales_flat_creditmemo',
                'increment_model'                => 'Magento\Eav\Model\Entity\Increment\Numeric',
                'increment_per_store'            => true,
                'attributes'                     => array()
            ),
            'shipment'                       => array(
                'entity_model'                   => 'Magento\Sales\Model\Resource\Order\Shipment',
                'table'                          => 'sales_flat_shipment',
                'increment_model'                => 'Magento\Eav\Model\Entity\Increment\Numeric',
                'increment_per_store'            => true,
                'attributes'                     => array()
            )
        );
        return $entities;
    }

    /**
     * Get Core Helper
     *
     * @return \Magento\Core\Helper\Data
     */
    public function getCoreData()
    {
        return $this->_coreData;
    }

    /**
     * Get config model
     *
     * @return \Magento\Core\Model\Config
     */
    public function getConfigModel()
    {
        return $this->_config;
    }
}
