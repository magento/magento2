<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Scan source code for DB schema or data updates for patch releases in non-actual branches
 * Backwards compatibility test
 */
namespace Magento\Test\Legacy;

class ModuleDBChangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $changedFilesPattern = __DIR__ . '/_files/changed_files*';

    /**
     * @var string
     */
    protected static $changedFileList = '';

    /**
     *  Set changed files paths and list for all projects
     */
    public static function setUpBeforeClass()
    {
        foreach (glob(self::$changedFilesPattern) as $changedFile) {
            self::$changedFileList .= file_get_contents($changedFile) . PHP_EOL;
        }
    }

    /**
     * Test changes for module.xml files
     */
    public function testModuleXmlFiles()
    {
        preg_match_all('|etc/module\.xml$|mi', self::$changedFileList, $matches);
        $this->assertEmpty(
            reset($matches),
            "module.xml changes for patch releases in non-actual branches are not allowed:" . PHP_EOL .
            implode(PHP_EOL, array_values(reset($matches)))
        );
    }

    /**
     * Test changes for files in Module Setup dir
     */
    public function testModuleSetupFiles()
    {
        preg_match_all('|app/code/Magento/[^/]+/Setup/[^/]+$|mi', self::$changedFileList, $matches);
        $this->assertEmpty(
            reset($matches),
            'Code with changes for DB schema or data in non-actual branches are not allowed:' . PHP_EOL .
            implode(PHP_EOL, array_values(reset($matches)))
        );
    }
}
