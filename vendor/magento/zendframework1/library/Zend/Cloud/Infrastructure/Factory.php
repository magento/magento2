<?php
/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/AbstractFactory.php';


/**
 * Factory for infrastructure adapters
 *
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Infrastructure_Factory extends Zend_Cloud_AbstractFactory
{
    const INFRASTRUCTURE_ADAPTER_KEY = 'infrastructure_adapter';

    /**
     * @var string Interface which adapter must implement to be considered valid
     */
    protected static $_adapterInterface = 'Zend_Cloud_Infrastructure_Adapter';

    /**
     * Constructor
     *
     * Private ctor - should not be used
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Retrieve an adapter instance
     *
     * @param  array $options
     * @return void
     */
    public static function getAdapter($options = array())
    {
        $adapter = parent::_getAdapter(self::INFRASTRUCTURE_ADAPTER_KEY, $options);

        if (!$adapter) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(sprintf(
                'Class must be specified using the "%s" key',
                self::INFRASTRUCTURE_ADAPTER_KEY
            ));
        } elseif (!$adapter instanceof self::$_adapterInterface) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(sprintf(
                'Adapter must implement "%s"', self::$_adapterInterface
            ));
        }
        return $adapter;
    }
}
