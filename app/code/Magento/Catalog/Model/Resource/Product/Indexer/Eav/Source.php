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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product\Indexer\Eav;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

/**
 * Catalog Product Eav Select and Multiply Select Attributes Indexer resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Source extends AbstractEav
{
    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper
    ) {
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource, $eavConfig, $eventManager);
    }

    /**
     * Initialize connection and define main index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav', 'entity_id');
    }

    /**
     * Retrieve indexable eav attribute ids
     *
     * @param bool $multiSelect
     * @return array
     */
    protected function _getIndexableAttributes($multiSelect)
    {
        $select = $this->_getReadAdapter()->select()->from(
            array('ca' => $this->getTable('catalog_eav_attribute')),
            'attribute_id'
        )->join(
            array('ea' => $this->getTable('eav_attribute')),
            'ca.attribute_id = ea.attribute_id',
            array()
        )->where(
            $this->_getIndexableAttributesCondition()
        );

        if ($multiSelect == true) {
            $select->where('ea.backend_type = ?', 'varchar')->where('ea.frontend_input = ?', 'multiselect');
        } else {
            $select->where('ea.backend_type = ?', 'int')->where('ea.frontend_input = ?', 'select');
        }

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Prepare data index for indexable attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     */
    protected function _prepareIndex($entityIds = null, $attributeId = null)
    {
        $this->_prepareSelectIndex($entityIds, $attributeId);
        $this->_prepareMultiselectIndex($entityIds, $attributeId);

        return $this;
    }

    /**
     * Prepare data index for indexable select attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     */
    protected function _prepareSelectIndex($entityIds = null, $attributeId = null)
    {
        $adapter = $this->_getWriteAdapter();
        $idxTable = $this->getIdxTable();
        // prepare select attributes
        if (is_null($attributeId)) {
            $attrIds = $this->_getIndexableAttributes(false);
        } else {
            $attrIds = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        /**@var $subSelect \Magento\Framework\DB\Select*/
        $subSelect = $adapter->select()->from(
            array('s' => $this->getTable('store')),
            array('store_id', 'website_id')
        )->joinLeft(
            array('d' => $this->getTable('catalog_product_entity_int')),
            '1 = 1 AND (d.store_id = 0 OR d.store_id = s.store_id)',
            array('entity_id', 'attribute_id', 'value')
        )->joinLeft(
            array('d2' => $this->getTable('catalog_product_entity_int')),
            sprintf(
                'd.entity_id = d2.entity_id AND d2.attribute_id = %s AND d2.value = %s AND d.store_id = d2.store_id',
                $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status')->getId(),
                ProductStatus::STATUS_ENABLED
            ),
            array()
        )->where(
            's.store_id != 0'
        )->where(
            'd.value IS NOT NULL'
        )->where(
            'd2.value IS NOT NULL'
        )->group(array(
            's.store_id', 's.website_id', 'd.entity_id', 'd.attribute_id', 'd.value'
        ));

        if (!is_null($entityIds)) {
            $subSelect->where('d.entity_id IN(?)', $entityIds);
        }

        /**@var $select \Magento\Framework\DB\Select*/
        $select = $adapter->select()->distinct(true)->from(
            array('pid' => new \Zend_Db_Expr(sprintf('(%s)', $subSelect->assemble()))),
            array()
        )->joinLeft(
            array('pis' => $this->getTable('catalog_product_entity_int')),
            'pis.entity_id = pid.entity_id AND pis.attribute_id = pid.attribute_id AND pis.store_id = pid.store_id',
            array()
        )->columns(
            array(
                'pid.entity_id',
                'pid.attribute_id',
                'pid.store_id',
                'value' => $adapter->getIfNullSql('pis.value', 'pid.value')
            )
        )->where(
            'pid.attribute_id IN(?)',
            $attrIds
        );

        $select->where($this->_resourceHelper->getIsNullNotNullCondition('pis.value', 'pid.value'));

        /**
         * Exclude attribute values that contains NULL
         */
        $select->where('NOT(pis.value IS NULL AND pis.value_id IS NOT NULL)');

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            array(
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('pid.entity_id'),
                'website_field' => new \Zend_Db_Expr('pid.website_id'),
                'store_field' => new \Zend_Db_Expr('pid.store_id')
            )
        );
        $query = $select->insertFromSelect($idxTable);
        $adapter->query($query);

        return $this;
    }

    /**
     * Prepare data index for indexable multiply select attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     */
    protected function _prepareMultiselectIndex($entityIds = null, $attributeId = null)
    {
        $adapter = $this->_getWriteAdapter();

        // prepare multiselect attributes
        if (is_null($attributeId)) {
            $attrIds = $this->_getIndexableAttributes(true);
        } else {
            $attrIds = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        // load attribute options
        $options = array();
        $select = $adapter->select()->from(
            $this->getTable('eav_attribute_option'),
            array('attribute_id', 'option_id')
        )->where(
            'attribute_id IN(?)',
            $attrIds
        );
        $query = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }

        // prepare get multiselect values query
        $productValueExpression = $adapter->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $adapter->select()->from(
            array('pvd' => $this->getTable('catalog_product_entity_varchar')),
            array('entity_id', 'attribute_id')
        )->join(
            array('cs' => $this->getTable('store')),
            '',
            array('store_id')
        )->joinLeft(
            array('pvs' => $this->getTable('catalog_product_entity_varchar')),
            'pvs.entity_id = pvd.entity_id AND pvs.attribute_id = pvd.attribute_id' . ' AND pvs.store_id=cs.store_id',
            array('value' => $productValueExpression)
        )->where(
            'pvd.store_id=?',
            $adapter->getIfNullSql('pvs.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        )->where(
            'cs.store_id!=?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->where(
            'pvd.attribute_id IN(?)',
            $attrIds
        );

        $statusCond = $adapter->quoteInto('=?', ProductStatus::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pvd.entity_id', 'cs.store_id', $statusCond);

        if (!is_null($entityIds)) {
            $select->where('pvd.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            array(
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('pvd.entity_id'),
                'website_field' => new \Zend_Db_Expr('cs.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            )
        );

        $i = 0;
        $data = array();
        $query = $select->query();
        while ($row = $query->fetch()) {
            $values = explode(',', $row['value']);
            foreach ($values as $valueId) {
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $data[] = array($row['entity_id'], $row['attribute_id'], $row['store_id'], $valueId);
                    $i++;
                    if ($i % 10000 == 0) {
                        $this->_saveIndexData($data);
                        $data = array();
                    }
                }
            }
        }

        $this->_saveIndexData($data);
        unset($options);
        unset($data);

        return $this;
    }

    /**
     * Save a data to temporary source index table
     *
     * @param array $data
     * @return $this
     */
    protected function _saveIndexData(array $data)
    {
        if (!$data) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $adapter->insertArray($this->getIdxTable(), array('entity_id', 'attribute_id', 'store_id', 'value'), $data);
        return $this;
    }

    /**
     * Retrieve temporary source index table name
     *
     * @param string|null $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_eav_idx');
        }
        return $this->getTable('catalog_product_index_eav_tmp');
    }
}
