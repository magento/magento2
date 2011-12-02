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


class Mage_Eav_Model_Convert_Adapter_Entity
    extends Mage_Dataflow_Model_Convert_Adapter_Abstract
{
    /**
     * Current store model
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    protected $_filter = array();
    protected $_joinFilter = array();
    protected $_joinAttr = array();
    protected $_attrToDb;
    protected $_joinField = array();

    /**
     * Retrieve store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_store)) {
            try {
                $this->_store = Mage::app()->getStore($this->getVar('store'));
            }
            catch (Exception $e) {
                $message = Mage::helper('Mage_Eav_Helper_Data')->__('Invalid store specified');
                $this->addException($message, Varien_Convert_Exception::FATAL);
                throw $e;
            }
        }
        return $this->_store->getId();
    }

    /**
     * @param $attrFilter - $attrArray['attrDB']   = ['like','eq','fromTo','dateFromTo]
     * @param $attrToDb    - attribute name to DB field
     * @return Mage_Eav_Model_Convert_Adapter_Entity
    */
    protected function _parseVars()
    {
        $varFilters = $this->getVars();
        $filters = array();
        foreach ($varFilters as $key => $val) {
            if (substr($key,0,6) === 'filter') {
                $keys = explode('/', $key, 2);
                $filters[$keys[1]] = $val;
            }
        }
        return $filters;
    }

    public function setFilter($attrFilterArray, $attrToDb = null, $bind = null, $joinType = null)
    {
        if (is_null($bind)) {
            $defBind = 'entity_id';
        }
        if (is_null($joinType)) {
            $joinType = 'LEFT';
        }

        $this->_attrToDb=$attrToDb;
        $filters = $this->_parseVars();

        foreach ($attrFilterArray as $key => $type) {
            if (is_array($type)) {
                if (isset($type['bind'])) {
                   $bind = $type['bind'];
                } else {
                   $bind = $defBind;
                }
                $type = $type['type'];
            }

            if ($type == 'dateFromTo' || $type == 'datetimeFromTo') {
                foreach ($filters as $k => $v) {
                    if (strpos($k, $key . '/') === 0) {
                        $split = explode('/', $k);
                        $filters[$key][$split[1]] = $v;
                    }
                }
            }

            $keyDB = (isset($this->_attrToDb[$key])) ? $this->_attrToDb[$key] : $key;

            $exp = explode('/',$key);

            if(isset($exp[1])){
                if(isset($filters[$exp[1]])){
                   $val = $filters[$exp[1]];
                   $this->setJoinAttr(array(
                       'attribute' => $keyDB,
                       'bind' => $bind,
                       'joinType' => $joinType
                    ));
                } else {
                    $val = null;
                }
                $keyDB = str_replace('/','_',$keyDB);
            } else {
                $val = isset($filters[$key]) ? $filters[$key] : null;
            }
            if (is_null($val)) {
                continue;
            }
            $attr = array();
            switch ($type){
                case 'eq':
                    $attr = array(
                        'attribute' => $keyDB,
                        'eq'        => $val
                    );
                    break;
                case 'like':
                    $attr = array(
                        'attribute' => $keyDB,
                        'like'      => '%'.$val.'%'
                    );
                    break;
                case 'startsWith':
                     $attr = array(
                         'attribute' => $keyDB,
                         'like'      => $val.'%'
                     );
                     break;
                case 'fromTo':
                    $attr = array(
                        'attribute' => $keyDB,
                        'from'      => $val['from'],
                        'to'        => $val['to']
                    );
                    break;
                case 'dateFromTo':
                    $attr = array(
                        'attribute' => $keyDB,
                        'from'      => $val['from'],
                        'to'        => $val['to'],
                        'date'      => true
                    );
                    break;
                case 'datetimeFromTo':
                    $attr = array(
                        'attribute' => $keyDB,
                        'from'      => isset($val['from']) ? $val['from'] : null,
                        'to'        => isset($val['to']) ? $val['to'] : null,
                        'datetime'  => true
                    );
                    break;
                default:
                break;
            }
            $this->_filter[] = $attr;
        }

        return $this;
    }

    public function getFilter()
    {
        return $this->_filter;
    }

    protected function getFieldValue($fields = array(), $name)
    {
        $result = array();
        if ($fields && $name) {
            foreach($fields as $index => $value) {
                $exp = explode('/', $index);
                if (isset($exp[1]) && $exp[0] == $name) {
                    $result[$exp[1]] = $value;
                }
            }
            if ($result) return $result;
        }
        return false;
    }

    public function setJoinAttr($joinAttr)
    {
        if(is_array($joinAttr)){
            $joinArrAttr = array();
            $joinArrAttr['attribute'] = isset($joinAttr['attribute']) ? $joinAttr['attribute'] : null;
            $joinArrAttr['alias'] = isset($joinAttr['attribute']) ? str_replace('/','_',$joinAttr['attribute']):null;
            $joinArrAttr['bind'] = isset($joinAttr['bind']) ? $joinAttr['bind'] : null;
            $joinArrAttr['joinType'] = isset($joinAttr['joinType']) ? $joinAttr['joinType'] : null;
            $joinArrAttr['storeId'] = isset($joinAttr['storeId']) ? $joinAttr['storeId'] : $this->getStoreId();
            $this->_joinAttr[] = $joinArrAttr;
        }

    }

    /**
     * Add join field
     *
     * @param array $joinField   Variable should be have view:
     *     Example:
     *         array(
     *            'alias'     => 'alias_table',
     *            'attribute' => 'table_name', //table name
     *            'field'     => 'field_name', //selected field name (optional)
     *            //bind main condition
     *            //left field use for joined table
     *            //and right field use for main table of collection
     *            //NOTE: around '=' cannot be used ' ' (space) because on the exploding not use space trimming
     *            'bind'      => 'self_item_id=other_id',
     *            'cond'      => 'alias_table.entity_id = e.entity_id', //additional condition (optional)
     *            'joinType'  => 'LEFT'
     *         )
     *     NOTE: Optional key must be have NULL at least
     * @return void
     */
    public function setJoinField($joinField)
    {
        if (is_array($joinField)) {
            $this->_joinField[] = $joinField;
        }
    }

    public function load()
    {
        $resourceOk = false;
        $entityResource = $this->getVar('entity_resource');
        if ($entityResource) {
            $resource = Mage::getResourceSingleton($entityResource);
            if ($resource instanceof Mage_Eav_Model_Entity_Interface) {
                $resourceOk = true;
            }
        }
        if (!$resourceOk) {
            $this->addException(
                Mage::helper('Mage_Eav_Helper_Data')->__('Invalid entity specified'),
                Varien_Convert_Exception::FATAL
            );
            return $this;
        }

        try {
            $collection = $this->_getCollectionForLoad($entityResource);

            if (isset($this->_joinAttr) && is_array($this->_joinAttr)) {
                foreach ($this->_joinAttr as $val) {
//                    print_r($val);
                    $collection->joinAttribute(
                        $val['alias'],
                        $val['attribute'],
                        $val['bind'],
                        null,
                        strtolower($val['joinType']),
                        $val['storeId']
                    );
                }
            }

            $filterQuery = $this->getFilter();
            if (is_array($filterQuery)) {
                foreach ($filterQuery as $val) {
                    $collection->addFieldToFilter(array($val));
                }
            }

            $joinFields = $this->_joinField;
            if (isset($joinFields) && is_array($joinFields)) {
                foreach ($joinFields as $field) {
//                  print_r($field);
                    $collection->joinField(
                        $field['alias'],
                        $field['attribute'],
                        $field['field'],
                        $field['bind'],
                        $field['cond'],
                        $field['joinType']);
               }
           }

           /**
            * Load collection ids
            */
           $entityIds = $collection->getAllIds();

           $message = Mage::helper('Mage_Eav_Helper_Data')->__("Loaded %d records", count($entityIds));
           $this->addException($message);
        }
        catch (Varien_Convert_Exception $e) {
            throw $e;
        }
        catch (Exception $e) {
            $message = Mage::helper('Mage_Eav_Helper_Data')->__('Problem loading the collection, aborting. Error: %s', $e->getMessage());
            $this->addException($message, Varien_Convert_Exception::FATAL);
        }

        /**
         * Set collection ids
         */
        $this->setData($entityIds);
        return $this;
    }

    /**
     * Retrieve collection for load
     *
     * @param string $entityResource
     * @return Mage_Eav_Model_Entity_Collection
     */
    protected function _getCollectionForLoad($entityResource)
    {
        return Mage::getResourceModel($entityResource . '_Collection');
    }

    public function save()
    {
        $collection = $this->getData();
        if ($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $this->addException(Mage::helper('Mage_Eav_Helper_Data')->__('Entity collections expected.'), Varien_Convert_Exception::FATAL);
        }

        $this->addException($collection->getSize().' records found.');

        if (!$collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $this->addException(Mage::helper('Mage_Eav_Helper_Data')->__('Entity collection expected.'), Varien_Convert_Exception::FATAL);
        }
        try {
            $i = 0;
            foreach ($collection->getIterator() as $model) {
                $model->save();
                $i++;
            }
            $this->addException(Mage::helper('Mage_Eav_Helper_Data')->__("Saved %d record(s).", $i));
        }
        catch (Varien_Convert_Exception $e) {
            throw $e;
        }
        catch (Exception $e) {
            $this->addException(Mage::helper('Mage_Eav_Helper_Data')->__('Problem saving the collection, aborting. Error: %s', $e->getMessage()),
                Varien_Convert_Exception::FATAL);
        }
        return $this;
    }
}
