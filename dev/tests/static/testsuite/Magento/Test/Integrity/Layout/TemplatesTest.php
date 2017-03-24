<?php
/**
 * Test layout declaration and usage of block elements
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Layout;

use Magento\Framework\App\Utility\Files;

class TemplatesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $templates = [];

    /**
     * Collect declarations of containers per layout file that have aliases
     */
    public static function setUpBeforeClass()
    {
        $count = 0;
        foreach (Files::init()->getLayoutFiles([], false) as $file) {
            $xml = simplexml_load_file($file);
            $templateElements = $xml->xpath('//block[@template]') ?: [];
            $fileTemplates = [];
            foreach ($templateElements as $node) {
                $fileTemplates[] = (string)$node['template'];
            }
            if (!empty($fileTemplates)) {
                self::$templates[$file] = $fileTemplates;
                $count += count($fileTemplates);
            }
        }
    }

    /**
     * Test that references to template files follows canonical Vendor_Module::path/to/template.phtml format.
     *
     * path/to/template.phtml Format is prohibited.
     * @return void
     */
    public function testTemplateFollowsCanonicalName()
    {
        $errors = [];
        foreach (self::$templates as $file => $templates) {
            foreach ($templates as $template) {
                if (!preg_match('/[A-Za-z0-9]_[A-Za-z0-9]+\:\:[A-Za-z0-9\\_\.]+/', $template)) {
                    if (!isset($errors[$file])) {
                        $errors[$file] = [];
                    }
                    $errors[$file][] = $template;
                }
            }
        }
        if (count($errors) > 0) {
            $message = 'Failed to assert that the template reference follows the canonical format '
                    . 'Vendor_Module::path/to/template.phtml. Following files haven\'t pass verification:' . PHP_EOL;
            foreach ($errors as $file => $wrongTemplates) {
                $message .= $file . ':' . PHP_EOL;
                $message .= '- ' . implode(PHP_EOL . '- ', $wrongTemplates) . PHP_EOL;
            }
            $this->fail($message);
        }
    }
}
