<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    private static $branchesFilesPattern = __DIR__ . '/../_files/branches*';

    /**
     * @var string
     */
    private static $changedFilesPattern = __DIR__ . '/../_files/changed_files*';

    /**
     * @var string
     */
    private static $changedFileList = '';

    /**
     * @var bool
     */
    private static $actualBranch = false;

    /**
     *  Set changed files paths and list for all projects
     */
    public static function setUpBeforeClass()
    {
        foreach (glob(self::$branchesFilesPattern) as $branchesFile) {
            //get the current branchname from the first line
            $branchName = trim(file($branchesFile)[0]);
            if ($branchName === 'develop') {
                self::$actualBranch = true;
            } else {
                //get current minor branch name
                preg_match('|^(\d+\.\d+)|', $branchName, $minorBranch);
                $branchName = $minorBranch[0];

                //get all version branches
                preg_match_all('|^(\d+\.\d+)|m', file_get_contents($branchesFile), $matches);

                //check is this a latest release branch
                self::$actualBranch = ($branchName == max($matches[0]));
            }
        }

        foreach (glob(self::$changedFilesPattern) as $changedFile) {
            self::$changedFileList .= file_get_contents($changedFile) . PHP_EOL;
        }
    }

    /**
     * Test changes for module.xml files
     */
    public function testModuleXmlFiles()
    {
        if (!self::$actualBranch) {
            preg_match_all('|etc/module\.xml$|mi', self::$changedFileList, $matches);
            $this->assertEmpty(
                reset($matches),
                'module.xml changes for patch releases in non-actual branches are not allowed:' . PHP_EOL .
                implode(PHP_EOL, array_values(reset($matches)))
            );
        }
    }

    /**
     * Test changes for files in Module Setup dir
     */
    public function testModuleSetupFiles()
    {
        if (!self::$actualBranch) {
            preg_match_all('|app/code/Magento/[^/]+/Setup/[^/]+$|mi', self::$changedFileList, $matches);
            $this->assertEmpty(
                reset($matches),
                'Code with changes for DB schema or data in non-actual branches are not allowed:' . PHP_EOL .
                implode(PHP_EOL, array_values(reset($matches)))
            );
        }
    }
}
