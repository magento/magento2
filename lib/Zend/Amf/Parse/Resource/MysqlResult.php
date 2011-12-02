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
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MysqlResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * This class will convert mysql result resource to array suitable for passing
 * to the external entities.
 *
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Resource_MysqlResult
{
    /**
     * @var array List of Mysql types with PHP counterparts
     *
     * Key => Value is Mysql type (exact string) => PHP type
     */
    static public $fieldTypes = array(
        "int" => "int",
        "timestamp" => "int",
        "year" => "int",
        "real" => "float",
    );
    /**
     * Parse resource into array
     *
     * @param resource $resource
     * @return array
     */
    public function parse($resource) {
        $result = array();
        $fieldcnt = mysql_num_fields($resource);
        $fields_transform = array();
        for($i=0;$i<$fieldcnt;$i++) {
            $type = mysql_field_type($resource, $i);
            if(isset(self::$fieldTypes[$type])) {
                $fields_transform[mysql_field_name($resource, $i)] = self::$fieldTypes[$type];
            }
        }

        while($row = mysql_fetch_object($resource)) {
            foreach($fields_transform as $fieldname => $fieldtype) {
               settype($row->$fieldname, $fieldtype);
            }
            $result[] = $row;
        }
        return $result;
    }
}
