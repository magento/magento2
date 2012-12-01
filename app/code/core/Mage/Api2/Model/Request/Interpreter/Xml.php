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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Request content interpreter XML adapter
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Request_Interpreter_Xml implements Mage_Api2_Model_Request_Interpreter_Interface
{
    /**
     * Default name for item of non-associative array
     */
    const ARRAY_NON_ASSOC_ITEM_NAME = 'data_item';

    /**
     * Load error string.
     *
     * Is null if there was no error while loading
     *
     * @var string
     */
    protected $_loadErrorStr = null;

    /**
     * Parse Request body into array of params
     *
     * @param string $body  Posted content from request
     * @return array
     * @throws Exception|Mage_Api2_Exception
     */
    public function interpret($body)
    {
        if (!is_string($body)) {
            throw new Exception(sprintf('Invalid data type "%s". String expected.', gettype($body)));
        }
        $body = false !== strpos($body, '<?xml') ? $body : '<?xml version="1.0"?>' . PHP_EOL . $body;

        // disable external entity loading to prevent possible vulnerability
        libxml_disable_entity_loader(true);
        set_error_handler(array($this, '_loadErrorHandler')); // Warnings and errors are suppressed
        $config = simplexml_load_string($body);
        restore_error_handler();
        // restore default behavior to make possible to load external entities
        libxml_disable_entity_loader(false);

        // Check if there was a error while loading file
        if ($this->_loadErrorStr !== null) {
            throw new Mage_Api2_Exception('Decoding error.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $xml = $this->_toArray($config);
        return $xml;
    }

    /**
     * Returns an associativearray from a SimpleXMLElement.
     *
     * @param  SimpleXMLElement $xmlObject Convert a SimpleXMLElement into an array
     * @return array
     */
    protected function _toArray(SimpleXMLElement $xmlObject)
    {
        $config = array();
        // Search for parent node values
        if (count($xmlObject->attributes()) > 0) {
            foreach ($xmlObject->attributes() as $key => $value) {
                $value = (string)$value;
                if (array_key_exists($key, $config)) {
                    if (!is_array($config[$key])) {
                        $config[$key] = array($config[$key]);
                    }
                    $config[$key][] = $value;
                } else {
                    $config[$key] = $value;
                }
            }
        }

        // Search for children
        if (count($xmlObject->children()) > 0) {
            foreach ($xmlObject->children() as $key => $value) {
                if (count($value->children()) > 0) {
                    $value = $this->_toArray($value);
                } else if (count($value->attributes()) > 0) {
                    $attributes = $value->attributes();
                    if (isset($attributes['value'])) {
                        $value = (string)$attributes['value'];
                    } else {
                        $value = $this->_toArray($value);
                    }
                } else {
                    $value = (string) $value;
                }
                if (array_key_exists($key, $config)) {
                    if (!is_array($config[$key]) || !array_key_exists(0, $config[$key])) {
                        $config[$key] = array($config[$key]);
                    }
                    $config[$key][] = $value;
                } else {
                    if (self::ARRAY_NON_ASSOC_ITEM_NAME != $key) {
                        $config[$key] = $value;
                    } else {
                        $config[] = $value;
                    }
                }
            }
        }

        return $config;
    }

    /**
     * Handle any errors from load xml
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     */
    protected function _loadErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($this->_loadErrorStr === null) {
            $this->_loadErrorStr = $errstr;
        } else {
            $this->_loadErrorStr .= (PHP_EOL . $errstr);
        }
    }
}
