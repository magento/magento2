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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Log_Formatter_Interface
 */
#require_once 'Zend/Log/Formatter/Interface.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_DynamicTableEntity
 */
#require_once 'Zend/Service/WindowsAzure/Storage/DynamicTableEntity.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Log
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Log_Formatter_WindowsAzure
    implements Zend_Log_Formatter_Interface
{
    /**
     * Write a message to the table storage
     *
     * @param  array $event
     *
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function format($event)
    {
        // partition key is the current date, represented as YYYYMMDD
        // row key will always be the current timestamp. These values MUST be hardcoded.
        $logEntity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity(
            date('Ymd'), microtime(true)
        );
        // Windows Azure automatically adds the timestamp, but the timezone is most of the time
        // different compared to the origin server's timezone, so add this timestamp aswell.
        $event['server_timestamp'] = $event['timestamp'];
        unset($event['timestamp']);

        foreach ($event as $key => $value) {
            if ((is_object($value) && !method_exists($value, '__toString'))
                || is_array($value)
            ) {
                $value = gettype($value);
            }
            $logEntity->{$key} = $value;
        }

        return $logEntity;
    }
}
