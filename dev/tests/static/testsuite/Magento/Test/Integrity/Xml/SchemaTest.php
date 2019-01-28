<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\Xml;

use Magento\Framework\Component\ComponentRegistrar;

class SchemaTest extends \PHPUnit\Framework\TestCase
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
                $this->assertCount(
                    2,
                    $schemaLocations,
                    'The XML file at ' . $filename . ' does not have a schema properly defined.  It should '
                    . 'have a xsi:noNamespaceSchemaLocation attribute defined with a URN path.  E.g. '
                    . 'xsi:noNamespaceSchemaLocation="urn:magento:framework:Relative_Path/something.xsd"'
                );

                try {
                    $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaLocations[1]);
                } catch (\Exception $exception) {
                    $errors = [$exception->__toString()];
                }
                $this->assertEmpty(
                    $errors,
                    "Error validating $filename against {$schemaLocations[1]}\n" . print_r($errors, true)
                );
            },
            $this->getXmlFiles()
        );
    }

    public function getSchemas()
    {
        $componentRegistrar = new ComponentRegistrar();
        $codeSchemas = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            $codeSchemas = array_merge($codeSchemas, $this->_getFiles($modulePath, '*.xsd'));
        }
        $libSchemas = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryPath) {
            $libSchemas = array_merge($libSchemas, $this->_getFiles($libraryPath, '*.xsd'));
        }
        return $this->_dataSet(array_merge($codeSchemas, $libSchemas));
    }

    public function getXmlFiles()
    {
        $componentRegistrar = new ComponentRegistrar();
        $codeXml = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            $codeXml = array_merge($codeXml, $this->_getFiles($modulePath, '*.xml', '/.\/Test\/./'));
        }
        $this->_filterSpecialCases($codeXml);
        $designXml = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themePath) {
            $designXml = array_merge($designXml, $this->_getFiles($themePath, '*.xml'));
        }
        $libXml = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryPath) {
            $libXml = array_merge($libXml, $this->_getFiles($libraryPath, '*.xml', '/.\/Test\/./'));
        }
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
            '#etc/countries.xml$#',
            '#conf/schema.xml$#',
            '#layout/swagger_index_index.xml$#',
            '#Doc/etc/doc/vars.xml$#',
            '#phpunit.xml$#',
            '#etc/db_schema.xml$#',
            '#Test/Mftf#',
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
