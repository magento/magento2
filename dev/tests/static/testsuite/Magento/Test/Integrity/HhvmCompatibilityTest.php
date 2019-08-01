<?php
/**
 * Hhvm ini_get/ini_set compatibility test
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;

class HhvmCompatibilityTest extends \PHPUnit\Framework\TestCase
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
        'default_charset',

        /*
          There is not way to specify calculation/serialization precision in hhvm.
          Adding to whitelist in order to align precisions in php.
        */
        'precision',
        'serialize_precision',
    ];

    /**
     * Whitelist of variables allowed in files.
     *
     * @var array
     */
    private $whitelistVarsInFiles = [
        'max_input_vars' => [
            'integration/testsuite/Magento/Swatches/Controller/Adminhtml/Product/AttributeTest.php',
            'integration/testsuite/Magento/Catalog/Controller/Adminhtml/Product/AttributeTest.php',
        ]
    ];

    /**
     * Test allowed directives.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testAllowedIniGetSetDirectives()
    {
        $deniedDirectives = [];
        foreach ($this->getFiles() as $file) {
            $fileDirectives = $this->parseDirectives($file);
            if ($fileDirectives) {
                $fileDeniedDirectives = array_diff($fileDirectives, $this->allowedDirectives);
                if ($fileDeniedDirectives) {
                    $deniedDirectivesInFile = array_unique($fileDeniedDirectives);
                    foreach ($deniedDirectivesInFile as $key => $deniedDirective) {
                        if (isset($this->whitelistVarsInFiles[$deniedDirective])) {
                            foreach ($this->whitelistVarsInFiles[$deniedDirective] as $whitelistFile) {
                                if (strpos($file, $whitelistFile) !== false) {
                                    unset($deniedDirectivesInFile[$key]);
                                }
                            }
                        }
                    }
                    if ($deniedDirectivesInFile) {
                        $deniedDirectives[$file] = $deniedDirectivesInFile;
                    }
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
            Files::init()->getFiles([BP . '/dev/'], '*.php')
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
        $message = 'HHVM-incompatible ini_get/ini_set options were found:';
        foreach ($deniedDirectives as $file => $fileDeniedDirectives) {
            $message .= "\n" . $file . ': [' . implode(', ', $fileDeniedDirectives) . ']';
        }
        return $message;
    }
}
