<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\App\Language;

use Magento\Framework\Component\ComponentRegistrar;

class Package extends \PHPUnit_Framework_TestCase
{
    /**
     * Read all lamguage.xml files and figure out the vendor and language code according from the file structure
     *
     * @return array
     */
    public static function readDeclarationFiles()
    {
        $result = [];
        $componentRegistrar = new ComponentRegistrar();
        $languagePaths = $componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE);
        foreach ($languagePaths as $languagePath) {
            foreach (glob($languagePath . "/language.xml") as $file) {
                preg_match('/.+\/(.*)\/(.*)\/language.xml$/', $file, $matches);
                $matches[0] = $file;
                $result[] = $matches;
            }
        }
        return $result;
    }
}
