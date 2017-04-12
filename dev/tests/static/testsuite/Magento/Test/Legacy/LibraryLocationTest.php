<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Verify that there are no files in the old locations of web and php libraries
 */
namespace Magento\Test\Legacy;

class LibraryLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Root path of Magento
     *
     * @var string
     */
    protected static $root;

    public static function setUpBeforeClass()
    {
        self::$root = BP;
    }

    public function testOldWebLibrariesLocation()
    {
        $oldLocation = self::$root . '/pub/lib';
        $this->assertFileNotExists($oldLocation, "The web libraries have been moved from 'pub/lib' to 'lib/web'");
    }

    public function testOldPhpLibrariesLocation()
    {
        $libLocation = self::$root . '/lib';

        $permittedEntries = [
            self::$root . '/lib/web',
            self::$root . '/lib/internal',
            self::$root . '/.htaccess',
        ];

        $entries = glob("{$libLocation}/*");
        $excessiveEntries = [];
        foreach ($entries as $entry) {
            $entry = str_replace('\\', '/', $entry);
            $permitted = false;
            foreach ($permittedEntries as $permittedEntry) {
                if ($permittedEntry == $entry) {
                    $permitted = true;
                    break;
                }
            }
            if (!$permitted) {
                $excessiveEntries[] = $entry;
            }
        }

        $this->assertEmpty(
            $excessiveEntries,
            "All files and directories have been moved from 'lib' to 'lib/internal'"
        );
    }
}
