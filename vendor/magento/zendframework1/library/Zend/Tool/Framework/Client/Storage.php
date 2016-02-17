<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Framework_Client_Storage_AdapterInterface
 */
#require_once 'Zend/Tool/Framework/Client/Storage/AdapterInterface.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Storage
{

    /**
     * @var Zend_Tool_Framework_Client_Storage_AdapterInterface
     */
    protected $_adapter = null;

    public function __construct($options = array())
    {
        if (isset($options['adapter'])) {
            $this->setAdapter($options['adapter']);
        }
    }

    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $storageAdapterClass = 'Zend_Tool_Framework_Client_Storage_' . ucfirst($adapter);
            Zend_Loader::loadClass($storageAdapterClass);
            $adapter = new $storageAdapterClass();
        }
        $this->_adapter = $adapter;
    }

    public function isEnabled()
    {
        return ($this->_adapter instanceof Zend_Tool_Framework_Client_Storage_AdapterInterface);
    }

    public function put($name, $value)
    {
        if (!$this->_adapter) {
            return false;
        }

        $this->_adapter->put($name, $value);

        return $this;
    }

    public function get($name, $defaultValue = false)
    {
        if (!$this->_adapter) {
            return false;
        }

        if ($this->_adapter->has($name)) {
            return $this->_adapter->get($name);
        } else {
            return $defaultValue;
        }

    }

    public function has($name)
    {
        if (!$this->_adapter) {
            return false;
        }

        return $this->_adapter->has($name);
    }

    public function remove($name)
    {
        if (!$this->_adapter) {
            return false;
        }

        $this->_adapter->remove($name);

        return $this;
    }

    public function getStreamUri($name)
    {
        if (!$this->_adapter) {
            return false;
        }

        return $this->_adapter->getStreamUri($name);
    }
}
