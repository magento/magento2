<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\App\Language;

/**
 * A test for language package declaration
 */
class PackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @param string $expectedVendor
     * @param string $expectedPackage
     * @dataProvider declaredConsistentlyDataProvider
     */
    public function testDeclaredConsistently($file, $expectedVendor, $expectedPackage)
    {
        $languageConfig = new \Magento\Framework\App\Language\Config(file_get_contents($file));
        $this->assertEquals($expectedVendor, $languageConfig->getVendor());
        $this->assertEquals($expectedPackage, $languageConfig->getPackage());
    }

    /**
     * @return array
     */
    public function declaredConsistentlyDataProvider()
    {
        $result = [];
        $root = \Magento\Framework\Test\Utility\Files::init()->getPathToSource();
        foreach (Package::readDeclarationFiles($root) as $row) {
            $result[] = $row;
        }
        return $result;
    }
}
