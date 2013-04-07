<?php
/**
 * Abstract entity API service.
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
abstract class Mage_Core_Service_Entity_Abstract
{
    /**
     * Returns model which operated by current service.
     *
     * @param mixed  $objectProductIdOrSku
     * @param string $fieldSetId
     * @return Varien_Object
     */
    abstract protected function _getObject($objectProductIdOrSku, $fieldSetId = '');

    /**
     * Returns schema with data.
     *
     * @param array         $data   Already fetched data from object
     * @param Varien_Object $object
     * @return array
     */
    abstract protected function _applySchema(array $data, Varien_Object $object);

    /**
     * Extract data out of the project object retrieved by ID.
     *
     * @param mixed  $objectId
     * @param string $fieldSetId
     * @return array
     */
    protected function _getData($objectId, $fieldSetId = '')
    {
        $data = array();
        $object = $this->_getObject($objectId, $fieldSetId);

        if ($object->getId()) {
            $data = $this->_getObjectData($object);
            $data = $this->_applySchema($data, $object);
        }

        return $data;
    }

    /**
     * Extract data from the loaded object and make it conform with schema.
     *
     * @param Varien_Object $object
     * @return array
     */
    protected function _getObjectData(Varien_Object $object)
    {
        $data = $object->getData();

        // Make camelCase out of underscore
        foreach ($data as $key => $value) {
            $camelCase = preg_replace_callback(
                '/_(.)/',
                function ($matches) { return strtoupper($matches[1]);},
                $key
            );

            if ($camelCase !== $key) {
                $data[$camelCase] = $data[$key];
                unset($data[$key]);
            }
        }

        $data = $this->_formatObjectData($data);

        return $data;
    }


    /**
     * Formats object's data so it represents an array on all levels.
     *
     * @param array $data
     * @return array
     */
    protected function _formatObjectData(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                //skip
                $data[$key] = null;
            } else if (is_array($value)) {
                $data[$key] = $this->_formatObjectData($value);
            }
        }

        return $data;
    }
}
