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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests to find various obsolete code usage
 * (deprecated and removed Magento 1 legacy methods, properties, classes, etc.)
 */
class Legacy_ObsoleteCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Message text that is used to render suggestions
     */
    const SUGGESTION_MESSAGE = 'Use "%s" instead.';

    /**
     * In-memory cache for the configuration files
     *
     * @var array
     */
    protected static $_configFilesCache = array();

    /**
     * @param string $file
     * @dataProvider phpFileDataProvider
     */
    public function testPhpFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoleteClasses($content, $file);
        $this->_testObsoleteMethods($content, $file);
        $this->_testObsoleteMethodArguments($content);
        $this->_testObsoleteProperties($content, $file);
        $this->_testObsoleteActions($content, $file);
        $this->_testObsoleteConstants($content, $file);
        $this->_testObsoletePropertySkipCalculate($content);
    }

    /**
     * @return array
     */
    public function phpFileDataProvider()
    {
        return Utility_Files::init()->getPhpFiles();
    }

    /**
     * @param string $file
     * @dataProvider xmlFileDataProvider
     */
    public function testXmlFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoleteClasses($content, $file);
    }

    /**
     * @return array
     */
    public function xmlFileDataProvider()
    {
        return Utility_Files::init()->getXmlFiles();
    }

    /**
     * @param string $file
     * @dataProvider jsFileDataProvider
     */
    public function testJsFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoletePropertySkipCalculate($content);
    }

    /**
     * @return array
     */
    public function jsFileDataProvider()
    {
        return Utility_Files::init()->getJsFiles();
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteClasses($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_classes*.php', $content, $file);
        foreach ($declarations as $entity => $suggestion) {
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS', $content,
                "Class '$entity' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteMethods($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_methods*.php', $content, $file);
        foreach ($declarations as $method => $suggestion) {
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($method, '/') . '\s*\(/iS', $content,
                "Method '$method' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteMethodArguments($content)
    {
        $this->_assertNotRegExp('/[^a-z\d_]getTypeInstance\s*\(\s*[^\)]+/iS', $content,
            'Backwards-incompatible change: method getTypeInstance() is not supposed to be invoked with any arguments.'
        );
        $this->_assertNotRegExp('/\->getUsedProductIds\(([^\)]+,\s*[^\)]+)?\)/', $content,
            'Backwards-incompatible change: method getUsedProductIds($product)'
            . ' must be invoked with one and only one argument - product model object'
        );
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteProperties($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_properties*.php', $content, $file);
        foreach ($declarations as $entity => $suggestion) {
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS', $content,
                "Property '$entity' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteActions($content)
    {
        $suggestion = 'Resizing images upon the client request is obsolete, use server-side resizing instead';
        $this->_assertNotRegExp('#[^a-z\d_/]catalog/product/image[^a-z\d_/]#iS', $content,
            "Action 'catalog/product/image' is obsolete. $suggestion"
        );
    }

    /**
     * @param string $content
     * @param string $file
     */
    protected function _testObsoleteConstants($content, $file)
    {
        $declarations = $this->_getRelevantConfigEntities('obsolete_constants*.php', $content, $file);
        foreach ($declarations as $entity => $suggestion) {
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS', $content,
                "Constant '$entity' is obsolete. $suggestion"
            );
        }
    }

    /**
     * @param string $content
     */
    protected function _testObsoletePropertySkipCalculate($content)
    {
        $this->_assertNotRegExp('/[^a-z\d_]skipCalculate[^a-z\d_]/iS', $content,
            "Configuration property 'skipCalculate' is obsolete."
        );
    }

    /**
     * Retrieve configuration items, whose 'class_scope' match to the content, in the following format:
     *   array(
     *     '<entity>' => '<suggestion>',
     *     ...
     *   )
     *
     * @param string $fileNamePattern
     * @param string $content
     * @param string $file
     * @return array
     */
    protected function _getRelevantConfigEntities($fileNamePattern, $content, $file)
    {
        $result = array();
        foreach ($this->_loadConfigFiles($fileNamePattern) as $entity => $info) {
            $class = $info['class_scope'];
            $regexp = '/(class|extends)\s+' . preg_quote($class, '/') . '(\s|;)/S';
            /* Note: strpos is used just to prevent excessive preg_match calls */
            if ($class && (!strpos($content, $class) || !preg_match($regexp, $content))) {
                continue;
            }
            if ($info['directory']) {
                if (0 !== strpos(str_replace('\\', '/', $file), str_replace('\\', '/', $info['directory']))) {
                    continue;
                }
            }
            $result[$entity] = $info['suggestion'];
        }
        return $result;
    }

    /**
     * Load configuration data from the files that match a glob-pattern
     *
     * @param string $fileNamePattern
     * @return array
     */
    protected function _loadConfigFiles($fileNamePattern)
    {
        if (isset(self::$_configFilesCache[$fileNamePattern])) {
            return self::$_configFilesCache[$fileNamePattern];
        }
        $config = array();
        foreach (glob(dirname(__FILE__) . '/_files/' . $fileNamePattern, GLOB_BRACE) as $configFile) {
            $config = array_merge($config, include($configFile));
        }
        $result = $this->_normalizeConfigData($config);
        self::$_configFilesCache[$fileNamePattern] = $result;
        return $result;
    }

    /**
     * Convert config data to the uniform format:
     *   array(
     *     '<entity>' => array(
     *       'suggestion' => '<suggestion>',
     *       'class_scope' => '<class_scope>',
     *     ),
     *     ...
     *   )
     *
     * @param array $config
     * @return array
     */
    protected function _normalizeConfigData(array $config)
    {
        $result = array();
        foreach ($config as $key => $value) {
            $row = array('suggestion' => null, 'class_scope' => null, 'directory' => null);
            if (is_string($key)) {
                $row = array_merge($row, $value);
                if ($row['suggestion']) {
                    $row['suggestion'] = sprintf(self::SUGGESTION_MESSAGE, $row['suggestion']);
                }
                if ($row['directory']) {
                    $row['directory'] = Utility_Files::init()->getPathToSource() . '/' . $row['directory'];
                }
                $result[$key] = $row;
            } else {
                $result[$value] = $row;
            }
        }
        return $result;
    }

    /**
     * Custom replacement for assertNotRegexp()
     *
     * In this particular test the original assertNotRegexp() cannot be used
     * because of too large text $content, which obfuscates tests output
     *
     * @param string $regex
     * @param string $content
     * @param string $message
     */
    protected function _assertNotRegexp($regex, $content, $message)
    {
        $this->assertSame(0, preg_match($regex, $content), $message);
    }
}
