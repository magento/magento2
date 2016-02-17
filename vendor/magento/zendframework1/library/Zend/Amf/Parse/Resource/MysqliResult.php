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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * This class will convert mysql result resource to array suitable for passing
 * to the external entities.
 *
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Resource_MysqliResult
{

    /**
     * mapping taken from http://forums.mysql.com/read.php?52,255868,255895#msg-255895
     */
    static public $mysqli_type = array(
        0 => "MYSQLI_TYPE_DECIMAL",
        1 => "MYSQLI_TYPE_TINYINT",
        2 => "MYSQLI_TYPE_SMALLINT",
        3 => "MYSQLI_TYPE_INTEGER",
        4 => "MYSQLI_TYPE_FLOAT",
        5 => "MYSQLI_TYPE_DOUBLE",
        7 => "MYSQLI_TYPE_TIMESTAMP",
        8 => "MYSQLI_TYPE_BIGINT",
        9 => "MYSQLI_TYPE_MEDIUMINT",
        10 => "MYSQLI_TYPE_DATE",
        11 => "MYSQLI_TYPE_TIME",
        12 => "MYSQLI_TYPE_DATETIME",
        13 => "MYSQLI_TYPE_YEAR",
        14 => "MYSQLI_TYPE_DATE",
        16 => "MYSQLI_TYPE_BIT",
        246 => "MYSQLI_TYPE_DECIMAL",
        247 => "MYSQLI_TYPE_ENUM",
        248 => "MYSQLI_TYPE_SET",
        249 => "MYSQLI_TYPE_TINYBLOB",
        250 => "MYSQLI_TYPE_MEDIUMBLOB",
        251 => "MYSQLI_TYPE_LONGBLOB",
        252 => "MYSQLI_TYPE_BLOB",
        253 => "MYSQLI_TYPE_VARCHAR",
        254 => "MYSQLI_TYPE_CHAR",
        255 => "MYSQLI_TYPE_GEOMETRY",
    );

    // Build an associative array for a type look up
    static $mysqli_to_php = array(
        "MYSQLI_TYPE_DECIMAL"     => 'float',
        "MYSQLI_TYPE_NEWDECIMAL"  => 'float',
        "MYSQLI_TYPE_BIT"         => 'integer',
        "MYSQLI_TYPE_TINYINT"     => 'integer',
        "MYSQLI_TYPE_SMALLINT"    => 'integer',
        "MYSQLI_TYPE_MEDIUMINT"   => 'integer',
        "MYSQLI_TYPE_BIGINT"      => 'integer',
        "MYSQLI_TYPE_INTEGER"     => 'integer',
        "MYSQLI_TYPE_FLOAT"       => 'float',
        "MYSQLI_TYPE_DOUBLE"      => 'float',
        "MYSQLI_TYPE_NULL"        => 'null',
        "MYSQLI_TYPE_TIMESTAMP"   => 'string',
        "MYSQLI_TYPE_INT24"       => 'integer',
        "MYSQLI_TYPE_DATE"        => 'string',
        "MYSQLI_TYPE_TIME"        => 'string',
        "MYSQLI_TYPE_DATETIME"    => 'string',
        "MYSQLI_TYPE_YEAR"        => 'string',
        "MYSQLI_TYPE_NEWDATE"     => 'string',
        "MYSQLI_TYPE_ENUM"        => 'string',
        "MYSQLI_TYPE_SET"         => 'string',
        "MYSQLI_TYPE_TINYBLOB"    => 'object',
        "MYSQLI_TYPE_MEDIUMBLOB"  => 'object',
        "MYSQLI_TYPE_LONGBLOB"    => 'object',
        "MYSQLI_TYPE_BLOB"        => 'object',
        "MYSQLI_TYPE_CHAR"        => 'string',
        "MYSQLI_TYPE_VARCHAR"     => 'string',
        "MYSQLI_TYPE_GEOMETRY"    => 'object',
        "MYSQLI_TYPE_BIT"         => 'integer',
    );

    /**
     * Parse resource into array
     *
     * @param resource $resource
     * @return array
     */
    public function parse($resource) {

        $result = array();
        $fieldcnt = mysqli_num_fields($resource);


        $fields_transform = array();

        for($i=0;$i<$fieldcnt;$i++) {
            $finfo = mysqli_fetch_field_direct($resource, $i);

            if(isset(self::$mysqli_type[$finfo->type])) {
                $fields_transform[$finfo->name] = self::$mysqli_to_php[self::$mysqli_type[$finfo->type]];
            }
        }

        while($row = mysqli_fetch_assoc($resource)) {
            foreach($fields_transform as $fieldname => $fieldtype) {
               settype($row[$fieldname], $fieldtype);
            }
            $result[] = $row;
        }
        return $result;
    }
}
