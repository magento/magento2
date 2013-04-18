<?php
/**
 * Abstract API service.
 * @todo Currently this class suitable only for entity-oriented services
 * @todo (i.e. those for which "item" and "items" methods make sense)
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Mage_Webapi_Service_Abstract
{
    /**
     * Array key which represents related data
     */
    const RELATED_DATA_KEY = '_related_data';

    /** @var Magento_ObjectManager */
    protected $_objectManager;

    /**
     * Contains related object data, which can not be retrieved through call to its getData() method
     */
    protected $_dictionary = array();

    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Returns model which operated by current service.
     *
     * @param mixed  $objectId
     * @param string $fieldsetId
     * @return Varien_Object
     */
    abstract protected function _getObject($objectId, $fieldsetId = '');

    /**
     * Get collection of objects of the current service.
     *
     * @param array  $objectIds
     * @param string $fieldsetId
     * @return Varien_Data_Collection_Db
     */
    abstract protected function _getObjectCollection(array $objectIds, $fieldsetId = '');

    /**
     * Returns data related to the object, which can not be retrieved by simply calling getData() method.
     *
     * @param mixed $object Object from which data is going to be retrieved
     * @return array
     */
    abstract protected function _getDictionary($object);

    /**
     * Wrapper around _getDictionary().
     *
     * @param mixed $object Object from which data is going to be retrieved
     * @return array
     */
    public function getDictionary($object)
    {
        if (empty($this->_dictionary)) {
            $this->_dictionary = $this->_getDictionary($object);
        }

        return $this->_dictionary;
    }

    /**
     * Extract data out of the project object retrieved by ID.
     *
     * @param mixed  $objectId
     * @param string $fieldsetId
     * @return array
     */
    protected function _getData($objectId, $fieldsetId = '')
    {
        $data = array();

        try {
            $object = $this->_getObject($objectId, $fieldsetId);

            if ($object->getId()) {
                $data = $this->_getObjectData($object);
            }
        } catch (Mage_Core_Exception $e) {
            // Empty array is going to be returned
        }

        return $data;
    }

    /**
     * Get data from several objects at once.
     *
     * @param array  $objectIds
     * @param string $fieldsetId
     * @return array
     */
    protected function _getCollectionData(array $objectIds, $fieldsetId = '')
    {
        $collection = $this->_getObjectCollection($objectIds, $fieldsetId);
        $dataCollection = array();

        foreach ($collection as $item) {
            /** @var $item Varien_Object */
            $dataCollection[] = $this->_getObjectData($item);
        }

        return $dataCollection;
    }

    /**
     * Adds additional data to the model data. Outside service may require some additional parameters, e.g. URL for
     * product. We can't just merge that into model data as user might have defined an attribute with same name, so
     * there already is going to be array key with same name. We're setting it to special section, name of which can
     * not be used as attribute name.
     *
     * @param array $mainData    Original model data (for product: price, sku, description, etc.)
     * @param array $relatedData Additional data to be added to the $mainData, such as product URL
     * @return array
     */
    protected function _setRelatedData(array $mainData, array $relatedData)
    {
        $mainData += array(
            static::RELATED_DATA_KEY => $relatedData
        );

        return $mainData;
    }

    /**
     * Extract data from the loaded object with service data added.
     *
     * @param Varien_Object $object
     * @return array
     */
    protected function _getObjectData(Varien_Object $object)
    {
        $data = $object->getData();
        $this->_formatObjectData($data);

        // Additional data, which is not contained in object's _data
        $data = $this->_setRelatedData($data, $this->getDictionary($object));

        return $data;
    }

    /**
     * Formats object's data so it represents an array on all levels.
     * @todo Decide what to do with objects
     *
     * @param array $data
     */
    protected function _formatObjectData(array &$data)
    {
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $data[$key] = '**OBJECT**';
            }
        }
    }
}
