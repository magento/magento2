<?php
/**
 * Test layout declaration and usage of block elements
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Layout;

use Magento\Framework\App\Utility\Files;

class TemplatesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected static $templates = [];

    /**
     * @var array
     */
    protected static $blockVirtualTypes = [];

    /**
     * Collect declarations of containers per layout file that have aliases
     */
    public static function setUpBeforeClass(): void
    {
        $count = 0;
        self::getBlockVirtualTypesWithDifferentModule();
        foreach (Files::init()->getLayoutFiles([], false) as $file) {
            $xml = simplexml_load_file($file);
            $blocks = $xml->xpath('//block[@template]') ?: [];
            $fileTemplates = [];
            foreach ($blocks as $block) {
                $fileTemplates[] = ['class' => (string)$block['class'], 'file' => (string)$block['template']];
            }
            if (!empty($fileTemplates)) {
                self::$templates[$file] = $fileTemplates;
                $count += count($fileTemplates);
            }
        }
    }

    /**
     * Test that references to template files follows canonical format.
     *
     * path/to/template.phtml Format is prohibited.
     * @return void
     */
    public function testTemplateFollowsCanonicalName()
    {
        $errors = [];
        $warnings = [];
        foreach (self::$templates as $file => $templates) {
            foreach ($templates as $templatePair) {
                if (!preg_match('/[A-Za-z0-9]_[A-Za-z0-9]+\:\:[A-Za-z0-9\\_\-\.]+/', $templatePair['file'])) {
                    if (!isset($errors[$file])) {
                        $errors[$file] = [];
                    }
                    $errors[$file][] = $templatePair['file'];
                } else {
                    if (isset(self::$blockVirtualTypes[$templatePair['class']])) {
                        $warnings[$file][] = $templatePair;
                    }
                }
            }
        }
        if (count($errors) > 0) {
            $message = 'Failed to assert that the template reference follows the canonical format '
                     . 'Vendor' . '_' . 'Module::path/to/template.phtml. Following files haven\'t pass verification:'
                     . PHP_EOL;
            foreach ($errors as $file => $wrongTemplates) {
                $message .= $file . ':' . PHP_EOL;
                $message .= '- ' . implode(PHP_EOL . '- ', $wrongTemplates) . PHP_EOL;
            }
            $this->fail($message);
        }
    }

    /**
     * Initialize array with the Virtual types for blocks
     *
     * Contains just those occurrences where base type and virtual type are located in different modules
     */
    private static function getBlockVirtualTypesWithDifferentModule()
    {
        $virtual = \Magento\Framework\App\Utility\Classes::getVirtualClasses();
        foreach ($virtual as $className => $resolvedName) {
            if (strpos($resolvedName, 'Block') !== false) {
                $matches = [];
                preg_match('/([A-Za-z0-9]+\\\\[A-Za-z0-9]+).*/', $className, $matches);
                if (count($matches) > 1) {
                    $oldModule = $matches[1];
                } else {
                    $oldModule = $className;
                }

                $matches = [];
                preg_match('/([A-Za-z0-9]+\\\\[A-Za-z0-9]+).*/', $resolvedName, $matches);
                $newModule = $matches[1];
                if ($oldModule != $newModule) {
                    self::$blockVirtualTypes[$className] = $resolvedName;
                }
            }
        }
    }
}
