<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests to ensure that all license blocks are represented by placeholders
 */
namespace Magento\Test\Legacy;

class LicenseTest extends \PHPUnit_Framework_TestCase
{
    public function testLegacyComment()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($filename) {
                $fileText = file_get_contents($filename);
                if (!preg_match_all('#/\*\*.+@copyright.+?\*/#s', $fileText, $matches)) {
                    return;
                }

                foreach ($matches[0] as $commentText) {
                    foreach (['Irubin Consulting Inc', 'DBA Varien', 'Magento Inc'] as $legacyText) {
                        $this->assertNotContains(
                            $legacyText,
                            $commentText,
                            "The license of file {$filename} contains legacy text."
                        );
                    }
                }
            },
            $this->legacyCommentDataProvider()
        );
    }

    public function legacyCommentDataProvider()
    {
        $root = \Magento\Framework\App\Utility\Files::init()->getPathToSource();
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS)
        );

        $rootFolderName = substr(strrchr($root, '/'), 1);
        $extensions = '(xml|css|php|phtml|js|dist|sample|additional)';
        $paths = [
            $rootFolderName . '/[^/]+\.' . $extensions,
            $rootFolderName . '/app/.+\.' . $extensions,
            $rootFolderName . '/dev/(?!tests/integration/tmp|tests/functional).+\.' . $extensions,
            $rootFolderName . '/lib/internal/(Mage|Magento|Varien)/.+\.' . $extensions,
            $rootFolderName . '/pub/.+\.' . $extensions,
        ];
        $regexIterator = new \RegexIterator($recursiveIterator, '#(' . implode(' | ', $paths) . ')$#x');

        $result = [];
        foreach ($regexIterator as $fileInfo) {
            $filename = (string)$fileInfo;
            if (!file_exists($filename) || !is_readable($filename)) {
                continue;
            }
            $result[] = [$filename];
        }
        return $result;
    }
}
