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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Default mapper for Varien_Objects
 */
class Mage_Webhook_Model_Mapper_Default implements Mage_Webhook_Model_Mapper_Interface
{
    const TOPIC_HEADER = 'Magento-Topic';

    const REDACTED = 'REDACTED';
    const CYCLE_DETECTED_MARK = '*** CYCLE DETECTED ***';

    protected $_topic;
    protected $_headers;
    protected $_data = null;
    protected $_objects;

    public function __construct($topic, array $objects)
    {
        $this->_topic = $topic;
        $this->_objects = $objects;
    }

    public function getTopic()
    {
        return $this->_topic;
    }

    public function getHeaders()
    {
        return array(self::TOPIC_HEADER => $this->_topic);
    }

    public function getData()
    {
        if (is_null($this->_data)) {
            $this->_mapData();
        }

        return $this->_data;
    }

    protected function _mapData()
    {
        $this->_data = array();
        foreach ($this->_objects as $name => $object) {
            if ($object instanceof Varien_Object || is_array($object)) {
                $this->_data[$name] = $this->_convertObjectToArray($object);
            } else {
                $this->_data[$name] = $object;
            }
        }
    }

    /**
     * Converts a Varien_Object into an array, including any children objects
     *
     * @param Varien_Object $obj
     * @param array $objects
     * @param bool $performRedaction If set to true will redact any fields returned from _getListOfRedactedFields.
     * @return array|string
     */
    protected function _convertObjectToArray($obj, &$objects = array(), $performRedaction = true)
    {
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (!empty($objects[$hash])) {
                return self::CYCLE_DETECTED_MARK;
            }
            $objects[$hash] = true;
            $data = $obj->getData();
        }
        else if (is_array($obj)) {
            $data = $obj;
        }

        $result = array();
        foreach ($data as $key=>$value) {
            if ($performRedaction && $this->_shouldRedact($key)) {
                $result[$key] = self::REDACTED;
            } elseif (is_scalar($value)) {
                $result[$key] = $value;
            } elseif (is_array($value)) {
                $result[$key] = $this->_convertObjectToArray($value, $objects, $performRedaction);
            } elseif ($value instanceof Varien_Object) {
                $result[$key] = $this->_convertObjectToArray($value, $objects, $performRedaction);
            }
        }
        return $result;
    }

    private $_redactedFields = array(
            'password',
            'password_hash',
        );
    /**
     * Returns a list of field names that should be redacted before sending any output
     */
    protected function _getListOfRedactedFields()
    {
        // TODO: it shouldn't be hardcoded. add an ability to configure it from XML config
        return $this->_redactedFields;
    }

    private function _shouldRedact($key)
    {
        $redacted = $this->_getListOfRedactedFields();
        if (is_string($key)) {
            return in_array($key, $redacted);
        } else {
            return false;
        }
    }
}