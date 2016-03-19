<?php
/**
 * Hhvm ini_get/ini_set compatibility test
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;

class HhvmCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $allowedDirectives = [
        'session.cookie_secure',
        'session.cookie_httponly',
        'session.use_cookies',
        'session.use_only_cookies',
        'session.referer_check',
        'session.save_path',
        'session.save_handler',
        'session.cookie_lifetime',
        'session.cookie_secure',
        'date.timezone',
        'memory_limit',
        'max_execution_time',
        'short_open_tag',
        'disable_functions',
        'asp_tags',
        'apc.enabled',
        'eaccelerator.enable',
        'mime_magic.magicfile',
        'display_errors',
        'default_socket_timeout',
        'pcre.recursion_limit',
        'default_charset'
    ];

    public function testAllowedIniGetSetDirectives()
    {
        $deniedDirectives = [];
        foreach ($this->getFiles() as $file) {
            $fileDirectives = $this->parseDirectives($file);
            if ($fileDirectives) {
                $fileDeniedDirectives = array_diff($fileDirectives, $this->allowedDirectives);
                if ($fileDeniedDirectives) {
                    $deniedDirectives[$file] = array_unique($fileDeniedDirectives);
                }
            }
        }
        if ($deniedDirectives) {
            $this->fail($this->createMessage($deniedDirectives));
        }
    }

    /**
     * @return array
     */
    protected function getFiles()
    {
        return \array_merge(
            Files::init()->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::INCLUDE_TESTS
                | Files::INCLUDE_NON_CLASSES
            ),
            Files::init()->getPhtmlFiles(false, false),
            Files::init()->getFiles([Files::init()->getPathToSource() . '/dev/'], '*.php')
        );
    }

    /**
     * @param string $file
     * @return null|array
     */
    protected function parseDirectives($file)
    {
        $content = file_get_contents($file);
        $pattern = '/ini_[g|s]et\(\s*[\'|"]([\w\._]+?)[\'|"][\s\w,\'"]*\)/';
        preg_match_all($pattern, $content, $matches);

        return $matches ? $matches[1] : null;
    }

    /**
     * @param array $deniedDirectives
     * @return string
     */
    protected function createMessage($deniedDirectives)
    {
        $rootPath = Files::init()->getPathToSource();
        $message = 'HHVM-incompatible ini_get/ini_set options were found:';
        foreach ($deniedDirectives as $file => $fileDeniedDirectives) {
            $message .= "\n" . str_replace($rootPath, '', $file) . ': [' . implode(', ', $fileDeniedDirectives) . ']';
        }
        return $message;
    }
}
