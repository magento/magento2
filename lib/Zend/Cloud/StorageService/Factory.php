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
 * @package    Zend_Cloud
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/AbstractFactory.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Factory extends Zend_Cloud_AbstractFactory
{
    const STORAGE_ADAPTER_KEY = 'storage_adapter';
    
    /**
     * @var string Interface which adapter must implement to be considered valid
     */
    protected static $_adapterInterface = 'Zend_Cloud_StorageService_Adapter';
    /**
     * Constructor
     * 
     * @return void
     */
    private function __construct()
    {
        // private ctor - should not be used
    }
    
    /**
     * Retrieve StorageService adapter
     * 
     * @param  array $options 
     * @return void
     */
    public static function getAdapter($options = array()) 
    {
        $adapter = parent::_getAdapter(self::STORAGE_ADAPTER_KEY, $options);
        if (!$adapter) {
            #require_once 'Zend/Cloud/StorageService/Exception.php';
            throw new Zend_Cloud_StorageService_Exception('Class must be specified using the \'' .
            self::STORAGE_ADAPTER_KEY . '\' key');
        } elseif (!$adapter instanceof self::$_adapterInterface) {
            #require_once 'Zend/Cloud/StorageService/Exception.php';
            throw new Zend_Cloud_StorageService_Exception(
                'Adapter must implement \'' . self::$_adapterInterface . '\''
            );
        }
        return $adapter;
    }
}
