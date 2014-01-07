<?php
/**
 * Validates that page_layouts references exsiting templates
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Magento\Theme\Config;

class ReferentialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array string[] $_templates Array of templates referenced from page_layouts
     */
    protected static $_templates;

    /** @var array all exsisting template files in the system */
    protected static $_templateFileNames = array();


    public static function setUpBeforeClass()
    {
        self::_populateTemplatesReferences();
        self::_populateTemplateFiles();
    }

    /**
     * Gathers all templates from page_layouts files
     */
    private static function _populateTemplatesReferences()
    {
        /**
         * @var array string[] $configFiles
         */
        $configFiles = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('page_layouts.xml', array(), false);
        /**
         * @var string $file
         */
        foreach ($configFiles as $file) {
            /**
             * @var \DOMDocument $dom
             */
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($file));

            $xpath = new \DOMXPath($dom);
            foreach ($xpath->query('/page_layouts/layouts/layout') as $layout) {
                foreach ($layout->childNodes as $layoutSubNode) {
                    if ($layoutSubNode->nodeName == 'template') {
                        self::$_templates[] = $layoutSubNode->nodeValue;
                    }
                }
            }
        }
    }

    /**
     * Gathers all tmplate file names
     */
    private static function _populateTemplateFiles()
    {
        $filesPaths = \Magento\TestFramework\Utility\Files::init()->getPhpFiles(false, false, true, false);
        foreach ($filesPaths as $filePath) {
            $filePathArray = explode('/', $filePath);
            $fileName = array_pop($filePathArray);
            if (!in_array($fileName, self::$_templateFileNames)) {
                self::$_templateFileNames[] = $fileName;
            }
        }
    }

    public function testTemplateExists()
    {
        $missing = array();
        foreach (self::$_templates as $templateName) {
            if (!in_array($templateName, self::$_templateFileNames)) {
                $missing[] = $templateName;
            }
        }
        if (!empty($missing)) {
            $message = sprintf(
                "These templates filenames used in page_layouts.xml doesn't correspond to any real template file: %s",
                implode(', ', $missing)
            );
            $this->fail($message);
        }
    }
}
