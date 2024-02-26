<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

/**
 * The Database expression converter
 */
class ExpressionConverter
{
    private const SHORT_HASH_LENGTH = 8;

    /**
     * Maximum length for many MySql identifiers, including database, table, trigger, and column names
     */
    public const MYSQL_IDENTIFIER_LEN = 64;

    /**
     * Dictionary maps common words in identifiers to abbreviations
     *
     * @var array
     */
    protected static $_translateMap = [
        'address' => 'addr',
        'admin' => 'adm',
        'attribute' => 'attr',
        'enterprise' => 'ent',
        'catalog' => 'cat',
        'category' => 'ctgr',
        'customer' => 'cstr',
        'notification' => 'ntfc',
        'product' => 'prd',
        'session' => 'sess',
        'user' => 'usr',
        'entity' => 'entt',
        'datetime' => 'dtime',
        'decimal' => 'dec',
        'varchar' => 'vchr',
        'index' => 'idx',
        'compare' => 'cmp',
        'bundle' => 'bndl',
        'option' => 'opt',
        'gallery' => 'glr',
        'media' => 'mda',
        'value' => 'val',
        'link' => 'lnk',
        'title' => 'ttl',
        'super' => 'spr',
        'label' => 'lbl',
        'website' => 'ws',
        'aggregat' => 'aggr',
        'minimal' => 'min',
        'inventory' => 'inv',
        'status' => 'sts',
        'agreement' => 'agrt',
        'layout' => 'lyt',
        'resource' => 'res',
        'directory' => 'dir',
        'downloadable' => 'dl',
        'element' => 'elm',
        'fieldset' => 'fset',
        'checkout' => 'chkt',
        'newsletter' => 'nlttr',
        'shipping' => 'shpp',
        'calculation' => 'calc',
        'search' => 'srch',
        'query' => 'qr',
    ];

    /**
     * Shorten name by abbreviating words
     *
     * @param string $name
     * @return string
     */
    public static function shortName($name)
    {
        return $name !== null ? strtr($name, self::$_translateMap) : '';
    }

    /**
     * Add an abbreviation to the dictionary, or replace if it already exists
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
     * Retrieves shorten entity name.
     *
     * Shorten the name of a MySql identifier, by abbreviating common words and hashing if necessary. Prepends the
     * given prefix to clarify what kind of entity the identifier represents, in case hashing is used.
     *
     * @param string $entityName
     * @param string $prefix
     * @return string
     */
    public static function shortenEntityName($entityName, $prefix)
    {
        if ($entityName === null) {
            return null;
        }

        $fullEntityName = $prefix . $entityName;
        if (strlen($fullEntityName) <= self::MYSQL_IDENTIFIER_LEN) {
            return $fullEntityName;
        }

        $shortName = ExpressionConverter::shortName($fullEntityName);
        if (strlen($shortName) <= self::MYSQL_IDENTIFIER_LEN) {
            return $shortName;
        }

        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $hash = md5($entityName);
        $hashedName = $prefix . $hash;
        if (strlen($hashedName) <= self::MYSQL_IDENTIFIER_LEN) {
            return $hashedName;
        }

        $trimmedHash = self::trimHash($hash, $prefix, self::MYSQL_IDENTIFIER_LEN);
        $trimmedName = $prefix . $trimmedHash;
        if (strlen($trimmedName) <= self::MYSQL_IDENTIFIER_LEN) {
            return $trimmedName;
        }

        // No prefix as a last resort
        return $hash;
    }

    /**
     * Remove superfluous characters from hash
     *
     * @param string $hash
     * @return string
     */
    private static function trimHash(string $hash): string {
        return substr($hash, 0, self::SHORT_HASH_LENGTH);
    }
}
