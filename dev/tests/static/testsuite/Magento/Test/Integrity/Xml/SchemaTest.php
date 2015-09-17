<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\Xml;

use \Magento\Framework\App\Bootstrap;
use Magento\Framework\Component\ComponentRegistrar;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testXmlFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filename
             */
            function ($filename) {
                $dom = new \DOMDocument();
                $xmlFile = file_get_contents($filename);
                $dom->loadXML($xmlFile);
                $errors = libxml_get_errors();
                libxml_clear_errors();
                $this->assertEmpty($errors, print_r($errors, true));

                $schemaLocations = [];
                preg_match('/xsi:noNamespaceSchemaLocation=\s*"(urn:[^"]+)"/s', $xmlFile, $schemaLocations);
                $this->assertEquals(
                    2,
                    count($schemaLocations),
                    'The XML file at ' . $filename . ' does not have a schema properly defined.  It should '
                    . 'have a xsi:noNamespaceSchemaLocation attribute defined with a URN path.  E.g. '
                    . 'xsi:noNamespaceSchemaLocation="urn:magento:library:framework:etc/something.xsd"'
                );

//                try {
                    $schemaFile = Bootstrap::create(BP, $_SERVER)->getObjectManager()
                        ->get('Magento\Framework\Config\Dom\UrnResolver')
                        ->getRealPath($schemaLocations[1]);
                    $this->assertFileExists($schemaFile, "$filename refers to an invalid schema $schemaFile.");
                    $errors = \Magento\TestFramework\Utility\Validator::validateXml($dom, $schemaFile);
                    $this->assertEmpty(
                        $errors,
                        "Error validating $filename against $schemaFile\n" . print_r($errors, true)
                    );
//                } catch (\UnexpectedValueException $e) {
//                    $this->fail($e->getMessage());
//                }

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
        $componentRegistrar = new ComponentRegistrar();
        $codeXml = $this->_getFiles(BP . '/app', '*.xml', '/.\/Test\/Unit\/./');
        $this->_filterSpecialCases($codeXml);
        $designXml = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themePath) {
            $designXml = array_merge($designXml, $this->_getFiles($themePath, '*.xml'));
        }
        $libXml = $this->_getFiles(BP . '/lib/Magento', '*.xml');
        return $this->_dataSet(array_merge($codeXml, $designXml, $libXml));
    }

    protected function _getFiles($dir, $pattern, $skipDirPattern = '')
    {
        $files = glob($dir . '/' . $pattern, GLOB_NOSORT);

        if (empty($skipDirPattern) || !preg_match($skipDirPattern, $dir)) {
            foreach (glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $newDir) {
                $files = array_merge($files, $this->_getFiles($newDir, $pattern, $skipDirPattern));
            }
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
            '#conf/schema.xml$#',
            '#conf/solrconfig.xml$#',
            '#layout/swagger_index_index.xml$#'
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
