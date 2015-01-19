<?php
/**
 * Backwards-incompatible changes in file system
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    public function testRelocations()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Directories may re-appear again during merging, therefore ensure they were properly relocated
             *
             * @param string $path
             */
            function ($path) {
                $this->assertFileNotExists(
                    \Magento\Framework\Test\Utility\Files::init()->getPathToSource() . '/' . $path
                );
            },
            $this->relocationsDataProvider()
        );
    }

    /**
     * @return array
     */
    public function relocationsDataProvider()
    {
        return [
            'Relocated to pub/errors' => ['errors'],
            'Eliminated with Magento_Compiler' => ['includes'],
            'Relocated to lib/web' => ['js'],
            'Relocated to pub/media' => ['media'],
            'Eliminated as not needed' => ['pkginfo'],
            'Dissolved into themes under app/design ' => ['skin'],
            'Dissolved into different modules\' view/frontend' => ['app/design/frontend/base'],
            'Dissolved into different modules\' view/email/*.html' => ['app/locale/en_US/template'],
            'The "core" code pool no longer exists. Use root namespace as specified in PSR-0 standard' => [
                'app/code/core',
            ],
            'The "local" code pool no longer exists. Use root namespace as specified in PSR-0 standard' => [
                'app/code/local',
            ],
            'The "community" code pool no longer exists. Use root namespace as specified in PSR-0 standard' => [
                'app/code/community',
            ],
            'Eliminated Magento/plushe theme' => ['app/design/frontend/Magento/plushe'],
        ];
    }

    public function testObsoleteDirectories()
    {
        $area = '*';
        $theme = '*';
        $root = \Magento\Framework\Test\Utility\Files::init()->getPathToSource();
        $dirs = glob("{$root}/app/design/{$area}/{$theme}/template", GLOB_ONLYDIR);
        $msg = [];
        if ($dirs) {
            $msg[] = 'Theme "template" directories are obsolete. Relocate files as follows:';
            foreach ($dirs as $dir) {
                $msg[] = str_replace($root, '', "{$dir} => " . realpath($dir . '/..') . '/Namespace_Module/*');
            }
        }

        $dirs = glob("{$root}/app/design/{$area}/{$theme}/layout", GLOB_ONLYDIR);
        if ($dirs) {
            $msg[] = 'Theme "layout" directories are obsolete. Relocate layout files into the root of theme directory.';
            $msg = array_merge($msg, $dirs);
        }

        if ($msg) {
            $this->fail(implode(PHP_EOL, $msg));
        }
    }

    public function testObsoleteViewPaths()
    {
        $allowedFiles = ['requirejs-config.js', 'layouts.xml'];
        $allowedThemeFiles = array_merge(
            $allowedFiles,
            ['composer.json', 'theme.xml', 'LICENSE.txt', 'LICENSE_AFL.txt']
        );
        $areas = '{frontend,adminhtml,base}';
        $ns = '*';
        $mod = '*';
        $pathsToCheck = [
            "app/code/{$ns}/{$mod}/view/{$areas}/*" => [
                'allowed_files' => $allowedFiles,
                'allowed_dirs'  => ['layout', 'page_layout', 'templates', 'web'],
            ],
            "app/design/{$areas}/{$ns}/{$mod}/*" => [
                'allowed_files' => $allowedThemeFiles,
                'allowed_dirs'  => ['layout', 'page_layout', 'templates', 'web', 'etc', 'i18n', 'media', '\w+_\w+'],
            ],
            "app/design/{$areas}/{$ns}/{$mod}/{$ns}_{$mod}/*" => [
                'allowed_files' => $allowedThemeFiles,
                'allowed_dirs'  => ['layout', 'page_layout', 'templates', 'web'],
            ],
        ];
        $errors = [];
        foreach ($pathsToCheck as $path => $allowed) {
            $allowedFiles = $allowed['allowed_files'];
            $allowedDirs = $allowed['allowed_dirs'];
            $foundFiles = glob(BP . '/' . $path, GLOB_BRACE);
            if (!$foundFiles) {
                $this->fail("Glob pattern returned empty result: {$path}");
            }
            foreach ($foundFiles as $file) {
                $baseName = basename($file);
                if (is_dir($file)) {
                    foreach ($allowedDirs as $allowedDir) {
                        if (preg_match("#^$allowedDir$#", $baseName)) {
                            continue 2;
                        }
                    }
                }
                if (in_array($baseName, $allowedFiles)) {
                    continue;
                }
                $errors[] = $file;
            }
        }
        if (!empty($errors)) {
            $this->fail(
                'Unexpected files or directories found. Make sure they are not at obsolete locations:'
                . PHP_EOL . implode(PHP_EOL, $errors)
            );
        }
    }
}
