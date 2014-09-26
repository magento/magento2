<?php
/**
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

namespace Magento\Test\Integrity\Xml;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testXmlFiles()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filename
             */
            function ($filename) {
                $dom = new \DOMDocument();
                $xmlFile = file_get_contents($filename);
                $dom->loadXML($xmlFile);
                $errors = libxml_get_errors();
                $this->assertEmpty($errors, print_r($errors, true));

                $schemaLocations = [];
                preg_match('/xsi:noNamespaceSchemaLocation=\s*"([^"]+)"/s', $xmlFile, $schemaLocations);
                $this->assertEquals(
                    2,
                    count($schemaLocations),
                    'The XML file at ' . $filename . ' does not have a schema properly defined.  It should '
                    . 'have a xsi:noNamespaceSchemaLocation attribute defined with a relative path.  E.g. '
                    . 'xsi:noNamespaceSchemaLocation="../../../lib/internal/Magento/Framework/etc/something.xsd"'
                );

                $schemaFile = dirname($filename).'/'.$schemaLocations[1];

                $this->assertFileExists($schemaFile, "$filename refers to an invalid schema $schemaFile.");

                $errors = \Magento\TestFramework\Utility\Validator::validateXml($dom, $schemaFile);
                $this->assertEmpty(
                    $errors,
                    "Error validating $filename against $schemaFile\n" . print_r($errors, true)
                );
            },
            $this->getXmlFiles()
        );
    }

    public function getSchemas()
    {
        $codeSchemas = $this->_getFiles(BP . '/app/code/Magento', '*.xsd');
        $libSchemas = $this->_getFiles(BP . '/lib/Magento', '*.xsd');
        return $this->_dataSet(array_merge($codeSchemas, $libSchemas));
    }

    public function getXmlFiles()
    {
        $codeXml = $this->_getFiles(BP . '/app', '*.xml');
        $this->_filterSpecialCases($codeXml);
        $designXml = $this->_getFiles(BP . '/app/design', '*.xml');
        $libXml = $this->_getFiles(BP . '/lib/Magento', '*.xml');
        return $this->_dataSet(array_merge($codeXml, $designXml, $libXml));
    }

    protected function _getFiles($dir, $pattern)
    {
        $files = glob($dir . '/' . $pattern, GLOB_NOSORT);
        foreach (glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $newDir) {
            $files = array_merge($files, $this->_getFiles($newDir, $pattern));
        }
        return $files;
    }

    /**
     * Files that are exempt from validation
     *
     * @param array &$files
     */
    private function _filterSpecialCases(&$files)
    {
        $list = [
            '#Dhl/etc/countries.xml$#',
            '#app/etc/local.xml$#',
            '#app/etc/[a-z]+/module.xml$#'
        ];
        foreach ($list as $pattern) {
            foreach ($files as $key => $value) {
                if (preg_match($pattern, $value)) {
                    unset($files[$key]);
                }
            }
        }
    }

    protected function _dataSet($files)
    {
        $data = [];
        foreach ($files as $file) {
            $data[substr($file, strlen(BP))] = [$file];
        }
        return $data;
    }
}
