<?php
/**
 * Test constructions of layout files
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
 * @category    tests
 * @package     static
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Integrity_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * Pattern for attribute elements, compatible with HTML ID
     */
    const HTML_ID_PATTERN = '/^[a-z][a-z\-\_\d]*$/';

    /**
     * @var array
     */
    protected static $_containerAliases = array();

    /**
     * @var array|bool
     */
    protected $_codeFrontendHandles = false;

    /**
     * @var array|bool
     */
    protected $_pageHandles = false;

    /**
     * Collect declarations of containers per layout file that have aliases
     */
    public static function setUpBeforeClass()
    {
        foreach (Utility_Files::init()->getLayoutFiles(array(), false) as $file) {
            $xml = simplexml_load_file($file);
            $containers = $xml->xpath('/layout//container[@as]') ?: array();
            foreach ($containers as $node) {
                $alias = (string)$node['as'];
                self::$_containerAliases[$file][(string)$node['name']] = $alias;
            }
        }
    }

    /**
     * Check that all handles declared in a theme layout are declared in code
     *
     * @param string $handleName
     * @dataProvider designHandlesDataProvider
     */

    public function testIsDesignHandleDeclaredInCode($handleName)
    {
        $this->assertArrayHasKey(
            $handleName,
            $this->_getCodeFrontendHandles(),
            "Handle '{$handleName}' is not declared in any module.'"
        );
    }

    /**
     * @return array
     */
    public function designHandlesDataProvider()
    {
        $files = Utility_Files::init()->getLayoutFiles(array(
            'include_code' => false,
            'area' => 'frontend'
        ));

        $handles = array();
        foreach (array_keys($files) as $path) {
            $xml = simplexml_load_file($path);
            $handleNodes = $xml->xpath('/layout/*') ?: array();
            foreach ($handleNodes as $handleNode) {
                $handles[] = $handleNode->getName();
            }
        }

        $result = array();
        foreach (array_unique($handles) as $handleName) {
            $result[] = array($handleName);
        }
        return $result;
    }

    /**
     * Returns information about handles that are declared in code for frontend
     *
     * @return array
     */
    protected function _getCodeFrontendHandles()
    {
        if ($this->_codeFrontendHandles) {
            return $this->_codeFrontendHandles;
        }

        $files = Utility_Files::init()->getLayoutFiles(array(
            'include_design' => false,
            'area' => 'frontend'
        ));
        foreach (array_keys($files) as $path) {
            $xml = simplexml_load_file($path);
            $handleNodes = $xml->xpath('/layout/*') ?: array();
            foreach ($handleNodes as $handleNode) {
                $isLabel = $handleNode->xpath('label');
                if (isset($handles[$handleNode->getName()]['label_count'])) {
                    $handles[$handleNode->getName()]['label_count'] += (int)$isLabel;
                } else {
                    $handles[$handleNode->getName()]['label_count'] = (int)$isLabel;
                }
            }
        }

        $this->_codeFrontendHandles = $handles;
        return $this->_codeFrontendHandles;
    }

    /**
     * Suppressing PHPMD because of complex logic of validation. The problem is architectural, rather than in the test
     *
     * @param string $file
     * @dataProvider layoutFilesDataProvider
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testHandleDeclaration($file)
    {
        if (strpos($file, 'Mage/XmlConnect')) {
            $this->markTestSkipped('Mage_XmlConnect module support is abandoned.');
        }
        $issues = array();
        $xml = simplexml_load_file($file);
        $handles = $xml->xpath('/layout/*');
        /** @var $node SimpleXMLElement */
        foreach ($handles as $node) {
            $handleMessage = "Handle '{$node->getName()}':";
            $type = $node['type'];
            $parent = $node['parent'];
            $owner = $node['owner'];
            if ($type) {
                switch ($type) {
                    case 'page':
                        if ($owner) {
                            $issues[] = "{$handleMessage} attribute 'owner' is inappropriate for page types";
                        }
                        break;
                    case 'fragment':
                        if ($parent) {
                            $issues[] = "{$handleMessage} attribute 'parent' is inappropriate for page fragment types";
                        }
                        if (!$owner) {
                            $issues[] = "{$handleMessage} no 'owner' specified for page fragment type";
                        }
                        break;
                    default:
                        $issues[] = "{$handleMessage} unknown type '{$type}'";
                        break;
                }
            } else {
                if ($node->xpath('child::label')) {
                    $issues[] = "{$handleMessage} 'label' child node is defined, but 'type' attribute is not";
                }
                if ($parent || $owner) {
                    $issues[] = "{$handleMessage} 'parent' or 'owner' is defined, but 'type' is not";
                }
            }
        }
        if (!empty($issues)) {
            $this->fail(sprintf("Issues found in handle declaration:\n%s\n", implode("\n", $issues)));
        }
    }

    /**
     * Suppressing PHPMD issues because this test is complex and it is not reasonable to separate it
     *
     * @param string $file
     * @dataProvider layoutFilesDataProvider
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testContainerDeclaration($file)
    {
        $xml = simplexml_load_file($file);
        $containers = $xml->xpath('/layout//container') ?: array();
        $errors = array();
        /** @var SimpleXMLElement $node */
        foreach ($containers as $node) {
            $nodeErrors = array();
            $attr = $node->attributes();
            if (!isset($attr['name'])) {
                $nodeErrors[] = '"name" attribute is not specified';
            } elseif (!preg_match('/^[a-z][a-z\-\_\d\.]*$/i', $attr['name'])) {
                $nodeErrors[] = 'specified value for "name" attribute is invalid';
            }
            if (!isset($attr['label']) || '' == $attr['label']) {
                $nodeErrors[] = '"label" attribute is not specified or empty';
            }
            if (isset($attr['as']) && !preg_match('/^[a-z\d\-\_]+$/i', $attr['as'])) {
                $nodeErrors[] = 'specified value for "as" attribute is invalid';
            }
            if (isset($attr['htmlTag']) && !preg_match('/^[a-z]+$/', $attr['htmlTag'])) {
                $nodeErrors[] = 'specified value for "htmlTag" attribute is invalid';
            }
            if (!isset($attr['htmlTag']) && (isset($attr['htmlId']) || isset($attr['htmlClass']))) {
                $nodeErrors[] = 'having "htmlId" or "htmlClass" attributes don\'t make sense without "htmlTag"';
            }
            if (isset($attr['htmlId']) && !preg_match(self::HTML_ID_PATTERN, $attr['htmlId'])) {
                $nodeErrors[] = 'specified value for "htmlId" attribute is invalid';
            }
            if (isset($attr['htmlClass']) && !preg_match(self::HTML_ID_PATTERN, $attr['htmlClass'])) {
                $nodeErrors[] = 'specified value for "htmlClass" attribute is invalid';
            }
            $allowedAttributes = array(
                'name', 'label', 'as', 'htmlTag', 'htmlId', 'htmlClass', 'module', 'output', 'before', 'after'
            );
            foreach ($attr as $key => $attribute) {
                if (!in_array($key, $allowedAttributes)) {
                    $nodeErrors[] = 'unexpected attribute "' . $key . '"';
                }
            }
            if ($nodeErrors) {
                $errors[] = "\n" . $node->asXML() . "\n - " . implode("\n - ", $nodeErrors);
            }
        }
        if ($errors) {
            $this->fail(implode("\n\n", $errors));
        }
    }

    /**
     * @return array
     */
    public function layoutFilesDataProvider()
    {
        return Utility_Files::init()->getLayoutFiles();
    }

    /**
     * @param string $alias
     * @dataProvider getChildBlockDataProvider
     */
    public function testBlocksNotContainers($alias)
    {
        foreach (self::$_containerAliases as $layoutFile => $containers) {
            try {
                $this->assertNotContains($alias, $containers,
                    "The getChildBlock('{$alias}') is used, but this alias is declared for container in {$layoutFile}"
                );
            } catch (PHPUnit_Framework_ExpectationFailedException $e) {
                $xml = simplexml_load_file($layoutFile);
                // if there is a block with this alias, then most likely it will be used and container is ok
                if (!$xml->xpath('/layout//block[@as="' . $alias . '"]')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getChildBlockDataProvider()
    {
        $result = array();
        foreach (Utility_Files::init()->getPhpFiles(true, false, true, false) as $file) {
            $aliases = Utility_Classes::getAllMatches(file_get_contents($file), '/\->getChildBlock\(\'([^\']+)\'\)/x');
            foreach ($aliases as $alias) {
                $result[$file] = array($alias);
            }
        }
        return $result;
    }
}
