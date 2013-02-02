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
     * @param string $content
     */
    protected function _testObsoleteClasses($content)
    {
        foreach (self::$_classes as $row) {
            list($entity, , $suggestion) = $row;
            $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($entity, '/') . '[^a-z\d_]/iS', $content,
                sprintf("Class '%s' is obsolete. Replacement suggestion: %s", $entity, $suggestion)
            );
        }
    }

    /**
     * Determine if content should be skipped based on specified class scope
     *
     * @param string $content
     * @param string $class
     * @return bool
     */
    protected function _isClassSkipped($content, $class)
    {
        $regexp = '/(class|extends)\s+' . preg_quote($class, '/') . '(\s|;)/S';
        /* Note: strpos is used just to prevent excessive preg_match calls */
        if ($class && (!strpos($content, $class) || !preg_match($regexp, $content))) {
            return true;
        }
        return false;
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteMethods($content)
    {
        foreach (self::$_methods as $row) {
            list($method, $class, $suggestion) = $row;
            if (!$this->_isClassSkipped($content, $class)) {
                $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($method, '/') . '\s*\(/iS', $content,
                    sprintf("Method '%s' is obsolete. Replacement suggestion: %s", $method, $suggestion)
                );
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
            list($attribute, $class, $suggestion) = $row;
            if (!$this->_isClassSkipped($content, $class)) {
                $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($attribute, '/') . '[^a-z\d_]/iS', $content,
                    sprintf("Class attribute '%s' is obsolete. Replacement suggestion: %s", $attribute, $suggestion)
                );
            }
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
     */
    protected function _testObsoleteConstants($content)
    {
        foreach (self::$_constants as $row) {
            list($constant, $class, $suggestion) = $row;
            if (!$this->_isClassSkipped($content, $class)) {
                $this->_assertNotRegExp('/[^a-z\d_]' . preg_quote($constant, '/') . '[^a-z\d_]/iS', $content,
                    sprintf("Constant '%s' is obsolete. Replacement suggestion: %s", $constant, $suggestion)
                );
            }
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
