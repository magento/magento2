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
class Zend_Tool_Framework_Client_Storage_Directory
    implements Zend_Tool_Framework_Client_Storage_AdapterInterface
{

    protected $_directoryPath = null;

    public function __construct($directoryPath)
    {
        if (!file_exists($directoryPath)) {
            throw new Zend_Tool_Framework_Client_Exception(__CLASS__ . ': the supplied directory does not exist');
        }
        $this->_directoryPath = $directoryPath;
    }

    public function put($name, $value)
    {
        return file_put_contents($this->_directoryPath . DIRECTORY_SEPARATOR . $name, $value);
    }

    public function get($name)
    {
        return file_get_contents($this->_directoryPath . DIRECTORY_SEPARATOR . $name);
    }

    public function has($name)
    {
        return file_exists($this->_directoryPath . DIRECTORY_SEPARATOR . $name);
    }

    public function remove($name)
    {
        return unlink($this->_directoryPath . DIRECTORY_SEPARATOR . $name);
    }

    public function getStreamUri($name)
    {
        return $this->_directoryPath . DIRECTORY_SEPARATOR . $name;
    }

}
