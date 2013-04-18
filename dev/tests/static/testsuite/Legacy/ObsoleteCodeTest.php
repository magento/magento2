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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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

    /**@#+
     * Lists of obsolete entities from fixtures
     *
     * @var array
     */
    protected static $_classes    = array();
    protected static $_constants  = array();
    protected static $_methods    = array();
    protected static $_attributes = array();
    /**#@-*/

    /**
     * Read fixtures into memory as arrays
     */
    public static function setUpBeforeClass()
    {
        $errors = array();
        self::_populateList(self::$_classes, $errors, 'obsolete_classes*.php', false);
        self::_populateList(self::$_constants, $errors, 'obsolete_constants*.php');
        self::_populateList(self::$_methods, $errors, 'obsolete_methods*.php');
        self::_populateList(self::$_attributes, $errors, 'obsolete_properties*.php');
        if ($errors) {
            $message = 'Duplicate patterns identified in list declarations:' . PHP_EOL . PHP_EOL;
            foreach ($errors as $file => $list) {
                $message .= $file . PHP_EOL;
                foreach ($list as $key) {
                    $message .= "    {$key}" . PHP_EOL;
                }
                $message .= PHP_EOL;
            }
            throw new Exception($message);
        }
    }

    /**
     * Read the specified file pattern and merge it with the list
     *
     * Duplicate entries will be recorded into errors array.
     *
     * @param array $list
     * @param array $errors
     * @param string $filePattern
     * @param bool $hasScope
     */
    protected static function _populateList(array &$list, array &$errors, $filePattern, $hasScope = true)
    {

        foreach (glob(__DIR__ . '/_files/' . $filePattern) as $file) {
            foreach (self::_readList($file) as $row) {
                list($item, $scope, $replacement) = self::_padRow($row, $hasScope);
                $key = "{$item}|{$scope}";
                if (isset($list[$key])) {
                    $errors[$file][] = $key;
                } else {
                    $list[$key] = array($item, $scope, $replacement);
                }
            }
        }
    }

    /**
     * Populate insufficient row elements regarding to whether the row supposed to have scope value
     *
     * @param array $row
     * @param bool $hasScope
     * @return array
     */
    protected static function _padRow($row, $hasScope)
    {
        if ($hasScope) {
            return array_pad($row, 3, '');
        }
        list($item, $replacement) = array_pad($row, 2, '');
        return array($item, '', $replacement);
    }

    /**
     * Isolate including a file into a method to reduce scope
     *
     * @param $file
     * @return array
     */
    protected static function _readList($file)
    {
        return include($file);
    }

    /**
     * @param string $file
     * @dataProvider phpFileDataProvider
     */
    public function testPhpFile($file)
    {
        $content = file_get_contents($file);
        $this->_testObsoleteClasses($content);
        $this->_testObsoleteMethods($content);
        $this->_testGetChildSpecialCase($content, $file);
        $this->_testGetOptionsSpecialCase($content);
        $this->_testObsoleteMethodArguments($content);
        $this->_testObsoleteProperties($content);
        $this->_testObsoleteActions($content);
        $this->_testObsoleteConstants($content);
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
     * Assert that obsolete classes are not used in the content
     *
     * @param string $content
     */
    protected function _testObsoleteClasses($content)
    {
        foreach (self::$_classes as $row) {
            list($class, , $replacement) = $row;
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($class, '/') . '[^a-z\d_]/iS', $content,
                $this->_suggestReplacement(sprintf("Class '%s' is obsolete.", $class), $replacement)
            );
        }
    }

    /**
     * Assert that obsolete methods or functions are not used in the content
     *
     * If class context is not specified, declaration/invocation of all functions or methods (of any class)
     * will be matched across the board
     *
     * If context is specified, only the methods will be matched as follows:
     * - usage of class::method
     * - usage of $this, self and static within the class and its descendants
     *
     * @param string $content
     */
    protected function _testObsoleteMethods($content)
    {
        foreach (self::$_methods as $row) {
            list($method, $class, $replacement) = $row;
            $quotedMethod = preg_quote($method, '/');
            if ($class) {
                $message = $this->_suggestReplacement("Method '{$class}::{$method}()' is obsolete.", $replacement);
                // without opening parentheses to match static callbacks notation
                $this->_assertNotRegExp(
                    '/' . preg_quote($class, '/') . '::\s*' . $quotedMethod . '[^a-z\d_]/iS',
                    $content,
                    $message
                );
                if ($this->_isSubclassOf($content, $class)) {
                    $this->_assertNotRegExp('/function\s*' . $quotedMethod . '\s*\(/iS', $content, $message);
                    $this->_assertNotRegExp('/this->' . $quotedMethod . '\s*\(/iS', $content, $message);
                    $this->_assertNotRegExp(
                        '/(self|static|parent)::\s*' . $quotedMethod . '\s*\(/iS', $content, $message
                    );
                }
            } else {
                $message = $this->_suggestReplacement("Function or method '{$method}()' is obsolete.", $replacement);
                $this->_assertNotRegExp('/function\s*' . $quotedMethod . '\s*\(/iS', $content, $message);
                $this->_assertNotRegExp('/[^a-z\d_]' . $quotedMethod . '\s*\(/iS', $content, $message);
            }
        }
    }

    /**
     * Special case: don't allow usage of getChild() method anywhere within app directory
     *
     * In Magento 1.x it used to belong only to abstract block (therefore all blocks)
     * At the same time, the name is pretty generic and can be encountered in other directories, such as lib
     *
     * @param string $content
     * @param string $file
     */
    protected function _testGetChildSpecialCase($content, $file)
    {
        if (0 === strpos($file, Utility_Files::init()->getPathToSource() . '/app/')) {
            $this->_assertNotRegexp('/[^a-z\d_]getChild\s*\(/iS', $content,
                'Block method getChild() is obsolete. Replacement suggestion: Mage_Core_Block_Abstract::getChildBlock()'
            );
        }
    }

    /**
     * Special case for ->getConfig()->getOptions()->
     *
     * @param string $content
     */
    protected function _testGetOptionsSpecialCase($content)
    {
        $this->_assertNotRegexp(
            '/getOptions\(\)\s*->get(Base|App|Code|Design|Etc|Lib|Locale|Js|Media'
                .'|Var|Tmp|Cache|Log|Session|Upload|Export)?Dir\(/S',
            $content,
            'The class Mage_Core_Model_Config_Options is obsolete. Replacement suggestion: Mage_Core_Model_Dir'
        );
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

        $this->_assertNotRegExp('#->_setActiveMenu\([\'"]([\w\d/_]+)[\'"]\)#Ui', $content,
            'Backwards-incompatible change: method _setActiveMenu()'
                . ' must be invoked with menu item identifier than xpath for menu item'
        );

        $this->assertEquals(0,
            preg_match('#Mage::getSingleton\([\'"]Mage_Backend_Model_Auth_Session[\'"]\)'
                . '([\s]+)?->isAllowed\(#Ui', $content),
            'Backwards-incompatible change: method isAllowed()'
                . ' must be invoked from Mage::getSingleton(\'Mage_Code_Model_Authorization\')->isAllowed($resource)'
        );

        $this->_assertNotRegExp(
            '#Mage::getSingleton\([\'"]Mage_Core_Model_Authorization[\'"]\)'
                . '([\s]+)?->isAllowed\([\'"]([\w\d/_]+)[\'"]\)#Ui',
            $content,
            'Backwards-incompatible change: method isAllowed()'
                . ' must be invoked with acl item identifier than xpath for acl item');
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteProperties($content)
    {
        foreach (self::$_attributes as $row) {
            list($attribute, $class, $replacement) = $row;
            if ($class) {
                if (!$this->_isSubclassOf($content, $class)) {
                    continue;
                }
                $fullyQualified = "{$class}::\${$attribute}";
            } else {
                $fullyQualified = $attribute;
            }
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($attribute, '/') . '[^a-z\d_]/iS', $content,
                $this->_suggestReplacement(sprintf("Class attribute '%s' is obsolete.", $fullyQualified), $replacement)
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
     * Assert that obsolete constants are not defined/used in the content
     *
     * Without class context, only presence of the literal will be checked.
     *
     * In context of a class, match:
     * - fully qualified constant notation (with class)
     * - usage with self::/parent::/static:: notation
     *
     * @param string $content
     */
    protected function _testObsoleteConstants($content)
    {
        foreach (self::$_constants as $row) {
            list($constant, $class, $replacement) = $row;
            if ($class) {
                $fullyQualified = "{$class}::{$constant}";
                $regex = preg_quote($fullyQualified, '/');
                if ($this->_isSubclassOf($content, $class)) {
                    $regex .= '|' . preg_quote("self::{$constant}", '/')
                        . '|' . preg_quote("parent::{$constant}", '/')
                        . '|' . preg_quote("static::{$constant}", '/');
                }
            } else {
                $fullyQualified = $constant;
                $regex = preg_quote($constant, '/');
            }
            $this->_assertNotRegExp('/[^a-z\d_]' . $regex . '[^a-z\d_]/iS', $content,
                $this->_suggestReplacement(sprintf("Constant '%s' is obsolete.", $fullyQualified), $replacement)
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
     * Analyze contents of a file to determine whether this is declaration of or a direct descendant of specified class
     *
     * @param string $content
     * @param string $class
     * @return bool
     */
    protected function _isSubclassOf($content, $class)
    {
        if (!$class) {
            return false;
        }
        return (bool)preg_match('/(class|extends|implements)\s+' . preg_quote($class, '/') . '(\s|;)/S', $content);
    }

    /**
     * Append a "suggested replacement" part to the string
     *
     * @param string $original
     * @param string $suggestion
     * @return string
     */
    private function _suggestReplacement($original, $suggestion)
    {
        if ($suggestion) {
            return "{$original} Suggested replacement: {$suggestion}";
        }
        return $original;
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
