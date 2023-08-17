<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Utility;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Utility for class names processing
 */
class Classes
{
    /**
     * virtual class declarations collected from the whole system
     *
     * @var array
     */
    protected static $_virtualClasses = [];

    /**
     * Find all unique matches in specified content using specified PCRE
     *
     * @param string $contents
     * @param string $regex
     * @param array &$result
     * @return array
     */
    public static function getAllMatches($contents, $regex, &$result = [])
    {
        preg_match_all($regex, $contents, $matches);

        array_shift($matches);
        foreach ($matches as $row) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $result = array_merge($result, $row);
        }
        $result = array_filter(
            array_unique($result),
            function ($value) {
                return !empty($value);
            }
        );
        return $result;
    }

    /**
     * Get XML node text values using specified xPath
     *
     * The node must contain specified attribute
     *
     * @param \SimpleXMLElement $xml
     * @param string $xPath
     * @return array
     */
    public static function getXmlNodeValues(\SimpleXMLElement $xml, $xPath)
    {
        $result = [];
        $nodes = $xml->xpath($xPath) ?: [];
        foreach ($nodes as $node) {
            $result[] = (string)$node;
        }
        return $result;
    }

    /**
     * Get XML node names using specified xPath
     *
     * @param \SimpleXMLElement $xml
     * @param string $xpath
     * @return array
     */
    public static function getXmlNodeNames(\SimpleXMLElement $xml, $xpath)
    {
        $result = [];
        $nodes = $xml->xpath($xpath) ?: [];
        foreach ($nodes as $node) {
            $result[] = $node->getName();
        }
        return $result;
    }

    /**
     * Get XML node attribute values using specified xPath
     *
     * @param \SimpleXMLElement $xml
     * @param string $xPath
     * @param string $attributeName
     * @return array
     */
    public static function getXmlAttributeValues(\SimpleXMLElement $xml, $xPath, $attributeName)
    {
        $result = [];
        $nodes = $xml->xpath($xPath) ?: [];
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
     * @param \SimpleXMLElement $xml
     * @return array
     */
    public static function collectClassesInConfig(\SimpleXMLElement $xml)
    {
        // @todo this method must be refactored after implementation of MAGETWO-7689 (valid configuration)
        $classes = self::getXmlNodeValues(
            $xml,
            '
            /config//resource_adapter | /config/*[not(name()="sections")]//class[not(ancestor::observers)]
                | //model[not(parent::connection)] | //backend_model | //source_model | //price_model
                | //model_token | //writer_model | //clone_model | //frontend_model | //working_model
                | //admin_renderer | //renderer | /config/*/di/preferences/*'
        );
        $classes = array_merge($classes, self::getXmlAttributeValues($xml, '//@backend_model', 'backend_model'));
        $classes = array_merge(
            $classes,
            self::getXmlNodeNames(
                $xml,
                '/logging/*/expected_models/* | /logging/*/actions/*/expected_models/* | /config/*/di/preferences/*'
            )
        );

        $classes = array_map([\Magento\Framework\App\Utility\Classes::class, 'getCallbackClass'], $classes);
        $classes = array_map('trim', $classes);
        $classes = array_unique($classes);
        $classes = array_filter(
            $classes,
            function ($value) {
                return !empty($value);
            }
        );

        return $classes;
    }

    /**
     * Find classes in a layout configuration XML-file
     *
     * @param \SimpleXMLElement $xml
     * @return array
     */
    public static function collectLayoutClasses(\SimpleXMLElement $xml)
    {
        $classes = self::getXmlAttributeValues($xml, '/layout//block[@class]', 'class');
        $classes = array_merge(
            $classes,
            self::getXmlNodeValues(
                $xml,
                '/layout//action/attributeType | /layout//action[@method="addTab"]/content
                | /layout//action[@method="addMergeSettingsBlockType"
                    or @method="addInformationRenderer"
                    or @method="addDatabaseBlock"]/*[2]
                | /layout//action[@method="setMassactionBlockName"]/name
                | /layout//action[@method="setEntityModelClass"]/code'
            )
        );
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
        $componentRegistrar = new ComponentRegistrar();
        $result = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $modulePath) {
            $pattern = '/^' . preg_quote($modulePath, '/') . '\/(' . $subTypePattern . '\/.+)\.php$/';
            foreach (Files::init()->getFiles([$modulePath], '*.php') as $file) {
                if ($file && preg_match($pattern, $file)) {
                    $partialFileName = substr($file, strlen($modulePath ?? '') + 1);
                    $partialFileName = substr($partialFileName, 0, strlen($partialFileName) - strlen('.php'));
                    $partialClassName = str_replace('/', '\\', $partialFileName);
                    $className = str_replace('_', '\\', $moduleName) . '\\' . $partialClassName;
                    $result[$className] = $moduleName;
                }
            }
        }
        return $result;
    }

    /**
     * Fetch virtual class declarations from DI configs
     *
     * @return array
     */
    public static function getVirtualClasses()
    {
        if (!empty(self::$_virtualClasses)) {
            return self::$_virtualClasses;
        }
        $configFiles = Files::init()->getDiConfigs();
        foreach ($configFiles as $fileName) {
            $configDom = new \DOMDocument();
            $configDom->load($fileName);
            $xPath = new \DOMXPath($configDom);
            $vTypes = $xPath->query('/config/virtualType');
            /** @var \DOMNode $virtualType */
            foreach ($vTypes as $virtualType) {
                $name = $virtualType->attributes->getNamedItem('name')->textContent;
                if (!$virtualType->attributes->getNamedItem('type')) {
                    continue;
                }
                $type = $virtualType->attributes->getNamedItem('type')->textContent;
                self::$_virtualClasses[$name] = $type;
            }
        }

        return self::$_virtualClasses;
    }

    /**
     * Check if instance is virtual type
     *
     * @param string $className
     * @return bool
     */
    public static function isVirtual($className)
    {
        //init virtual classes if necessary
        self::getVirtualClasses();

        return array_key_exists($className, self::$_virtualClasses);
    }

    /**
     * Get real type name for virtual type
     *
     * @param string $className
     * @return string
     */
    public static function resolveVirtualType($className)
    {
        if (false == self::isVirtual($className)) {
            return $className;
        }

        $resolvedName = self::$_virtualClasses[$className];
        return self::resolveVirtualType($resolvedName);
    }

    /**
     * Check class is auto-generated
     *
     * @param string $className
     * @return bool
     */
    public static function isAutogenerated($className)
    {
        if ($className && preg_match(
            '/.*\\\\[a-zA-Z0-9]{1,}(Factory|SearchResults|DataBuilder|Extension|ExtensionInterface)$/',
            $className
        )
            || preg_match('/Magento\\\\[\w]+\\\\(Test\\\\(Page|Fixture))\\\\/', $className)
            || preg_match('/.*\\\\[a-zA-Z0-9]{1,}\\\\Proxy$/', $className)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Scan contents as PHP-code and find class name occurrences
     *
     * @param string $contents
     * @param array &$classes
     * @return array
     */
    public static function collectPhpCodeClasses($contents, &$classes = [])
    {
        self::getAllMatches(
            $contents,
            '/
            # ::getModel ::getSingleton ::getResourceModel ::getResourceSingleton
            \:\:get(?:Resource)?(?:Model | Singleton)\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # addBlock createBlock getBlockSingleton
            | (?:addBlock | createBlock | getBlockSingleton)\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # various methods, first argument
            | \->(?:initReport | setEntityModelClass
                | setAttributeModel | setBackendModel | setFrontendModel | setSourceModel | setModel
            )\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # various methods, second argument
            | \->add(?:ProductConfigurationHelper | OptionsRenderCfg)\(.+,\s*[\'"]([^\'"]+)[\'"]\s*[\),]

            # models in install or setup
            | [\'"](?:resource_model | attribute_model | entity_model | entity_attribute_collection
                | source | backend | frontend | input_renderer | frontend_input_renderer
            )[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]

            # misc
            | function\s_getCollectionClass\(\)\s+{\s+return\s+[\'"]([a-z\d_\/]+)[\'"]
            | (?:_parentResourceModelName | _checkoutType | _apiType)\s*=\s*\'([a-z\d_\/]+)\'
            | \'renderer\'\s*=>\s*\'([a-z\d_\/]+)\'
            | protected\s+\$_(?:form|info|backendForm|iframe)BlockType\s*=\s*[\'"]([^\'"]+)[\'"]

            /Uix',
            $classes
        );

        // check ->_init | parent::_init
        $skipForInit = implode(
            '|',
            [
                'id',
                '[\w\d_]+_id',
                'pk',
                'code',
                'status',
                'serial_number',
                'entity_pk_value',
                'currency_code',
                'unique_key'
            ]
        );
        self::getAllMatches(
            $contents,
            '/
            (?:parent\:\: | \->)_init\(\s*[\'"]([^\'"]+)[\'"]\s*\)
            | (?:parent\:\: | \->)_init\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]((?!(' .
            $skipForInit .
            '))[^\'"]+)[\'"]\s*\)
            /Uix',
            $classes
        );
        return $classes;
    }

    /**
     * Retrieve module name by class
     *
     * @param string $class
     * @return string
     */
    public static function getClassModuleName($class)
    {
        $parts = explode('\\', trim($class ?: '', '\\'));
        return $parts[0] . '_' . $parts[1];
    }
}
