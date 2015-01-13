<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\App\Language;

class Package extends \PHPUnit_Framework_TestCase
{
    /**
     * Read all lamguage.xml files and figure out the vendor and language code according from the file structure
     *
     * @param string $rootDir
     * @return array
     */
    public static function readDeclarationFiles($rootDir)
    {
        $result = [];
        foreach (glob("{$rootDir}/app/i18n/*/*/language.xml") as $file) {
            preg_match('/.+\/(.*)\/(.*)\/language.xml$/', $file, $matches);
            $matches[0] = $file;
            $result[] = $matches;
        }
        return $result;
    }
}
