<?php
/**
 * DB expression converter
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

class ExpressionConverter
{
    /**
     * Maximum length for many MySql identifiers, including database, table, trigger, and column names
     */
    const MYSQL_IDENTIFIER_LEN = 64;

    /**
     * Dictionary for generate short name
     *
     * @var array
     */
    protected static $_translateMap = [
        'address'       => 'addr',
        'admin'         => 'adm',
        'attribute'     => 'attr',
        'enterprise'    => 'ent',
        'catalog'       => 'cat',
        'category'      => 'ctgr',
        'customer'      => 'cstr',
        'notification'  => 'ntfc',
        'product'       => 'prd',
        'session'       => 'sess',
        'user'          => 'usr',
        'entity'        => 'entt',
        'datetime'      => 'dtime',
        'decimal'       => 'dec',
        'varchar'       => 'vchr',
        'index'         => 'idx',
        'compare'       => 'cmp',
        'bundle'        => 'bndl',
        'option'        => 'opt',
        'gallery'       => 'glr',
        'media'         => 'mda',
        'value'         => 'val',
        'link'          => 'lnk',
        'title'         => 'ttl',
        'super'         => 'spr',
        'label'         => 'lbl',
        'website'       => 'ws',
        'aggregat'      => 'aggr',
        'minimal'       => 'min',
        'inventory'     => 'inv',
        'status'        => 'sts',
        'agreement'     => 'agrt',
        'layout'        => 'lyt',
        'resource'      => 'res',
        'directory'     => 'dir',
        'downloadable'  => 'dl',
        'element'       => 'elm',
        'fieldset'      => 'fset',
        'checkout'      => 'chkt',
        'newsletter'    => 'nlttr',
        'shipping'      => 'shpp',
        'calculation'   => 'calc',
        'search'        => 'srch',
        'query'         => 'qr',
    ];

    /**
     * Convert name using dictionary
     *
     * @param string $name
     * @return string
     */
    public static function shortName($name)
    {
        return strtr($name, self::$_translateMap);
    }

    /**
     * Add or replace translate to dictionary
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public static function addTranslate($from, $to)
    {
        self::$_translateMap[$from] = $to;
    }

    /**
     * @param string $tableName
     * @param string $prefix
     * @return string
     */
    public static function shortenEntityName($tableName, $prefix)
    {
        if (strlen($tableName) > self::MYSQL_IDENTIFIER_LEN) {
            $shortName = ExpressionConverter::shortName($tableName);
            if (strlen($shortName) > self::MYSQL_IDENTIFIER_LEN) {
                $hash = md5($tableName);
                if (strlen($prefix . $hash) > self::MYSQL_IDENTIFIER_LEN) {
                    $tableName = self::trimHash($hash, $prefix, self::MYSQL_IDENTIFIER_LEN);
                } else {
                    $tableName = $prefix . $hash;
                }
            } else {
                $tableName = $shortName;
            }
        }
        return $tableName;
    }

    /**
     * Remove superfluous characters from hash.
     *
     * @param  string $hash
     * @param  string $prefix
     * @param  int $maxCharacters
     * @return string
     */
    private static function trimHash($hash, $prefix, $maxCharacters)
    {
        $diff        = strlen($hash) + strlen($prefix) -  $maxCharacters;
        $superfluous = $diff / 2;
        $odd         = $diff % 2;
        $hash        = substr($hash, $superfluous, - ($superfluous + $odd));
        return $hash;
    }
}
