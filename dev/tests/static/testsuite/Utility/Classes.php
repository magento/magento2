<?php
/**
 * A helper for handling Magento-specific class names in various use cases
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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/Files.php';

class Utility_Classes
{
    /**
     * Find all unique matches in specified content using specified PCRE
     *
     * @param string $contents
     * @param string $regex
     * @param array &$result
     * @return array
     */
    public static function getAllMatches($contents, $regex, &$result = array())
    {
        preg_match_all($regex, $contents, $matches);

        array_shift($matches);
        foreach ($matches as $row) {
            $result = array_merge($result, $row);
        }
        $result = array_filter(array_unique($result), function($value) {
            return !empty($value);
        });
        return $result;
    }

    /**
     * Get XML node text values or values of specified attribute using specified xPath
     *
     * The node must contain specified attribute
     *
     * @param SimpleXMLElement $xml
     * @param string $xPath
     * @return array
     */
    public static function getXmlNodeValues(SimpleXMLElement $xml, $xPath)
    {
        $result = array();
        $nodes = $xml->xpath($xPath) ?: array();
        foreach ($nodes as $node) {
            $result[] = (string)$node;
        }
        return $result;
    }

    /**
     * Get XML node attribute values using specified xPath
     *
     * @param SimpleXMLElement $xml
     * @param string $xPath
     * @param string $attributeName
     * @return array
     */
    public static function getXmlAttributeValues(SimpleXMLElement $xml, $xPath, $attributeName)
    {
        $result = array();
        $nodes = $xml->xpath($xPath) ?: array();
        foreach ($nodes as $node) {
            $node = (array)$node;
            if (isset($node['@attributes'][$attributeName])) {
                $result[] = $node['@attributes'][$attributeName];
            }
        }
        return $result;
    }

    /**
     * Extract class name from a conventional callback specification "Class::method"
     *
     * @param string $callbackName
     * @return string
     */
    public static function getCallbackClass($callbackName)
    {
        $class = explode('::', $callbackName);
        return $class[0];
    }

    /**
     * Find classes in a configuration XML-file (assumes any files under Namespace/Module/etc/*.xml)
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    public static function collectClassesInConfig(SimpleXMLElement $xml)
    {
        $classes = self::getXmlNodeValues($xml, '
            /config//resource_adapter | /config/*[not(name()="sections")]//class | //model
                | //backend_model | //source_model | //price_model | //model_token | //writer_model | //clone_model
                | //frontend_model | //working_model | //admin_renderer | //renderer'
        );
        $classes = array_merge($classes, self::getXmlAttributeValues($xml, '//@backend_model', 'backend_model'));
        $nodes = $xml->xpath('/logging/*/expected_models/* | /logging/*/actions/*/expected_models/*') ?: array();
        foreach ($nodes as $node) {
            $classes[] = $node->getName();
        }

        $classes = array_map(array('Utility_Classes', 'getCallbackClass'), $classes);
        $classes = array_map('trim', $classes);
        $classes = array_unique($classes);
        $classes = array_filter($classes, function ($value) {
            return !empty($value);
        });

        return $classes;
    }

    /**
     * Find classes in a layout configuration XML-file
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    public static function collectLayoutClasses(SimpleXMLElement $xml)
    {
        $classes = self::getXmlAttributeValues($xml, '/layout//block[@type]', 'type');
        $classes = array_merge($classes, self::getXmlNodeValues($xml,
            '/layout//action/attributeType | /layout//action[@method="addTab"]/content
                | /layout//action[@method="addRenderer" or @method="addItemRender" or @method="addColumnRender"
                    or @method="addPriceBlockType" or @method="addMergeSettingsBlockType"
                    or @method="addInformationRenderer" or @method="addOptionRenderer" or @method="addRowItemRender"
                    or @method="addDatabaseBlock"]/*[2]
                | /layout//action[@method="setMassactionBlockName" or @method="addProductConfigurationHelper"]/name
                | /layout//action[@method="setEntityModelClass"]/code'
        ));
        return array_unique($classes);
    }

    /**
     * Scan application source code and find classes
     *
     * Sub-type pattern allows to distinguish "type" of a class within a module (for example, Block, Model)
     * Returns array(<class> => <module>)
     *
     * @param string $subTypePattern
     * @return array
     */
    public static function collectModuleClasses($subTypePattern = '[A-Za-z]+')
    {
        $pattern = '/^' . preg_quote(Utility_Files::init()->getPathToSource(), '/')
            . '\/app\/code\/[a-z]+\/([A-Za-z]+)\/([A-Za-z]+)\/(' . $subTypePattern . '\/.+)\.php$/';
        $result = array();
        foreach (Utility_Files::init()->getPhpFiles(true, false, false, false) as $file) {
            if (preg_match($pattern, $file, $matches)) {
                $module = "{$matches[1]}_{$matches[2]}";
                $class = "{$module}_" . str_replace('/', '_', $matches[3]);
                $result[$class] = $module;
            }
        }
        return $result;
    }
}
