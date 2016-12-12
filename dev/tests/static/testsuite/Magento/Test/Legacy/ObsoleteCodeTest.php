<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests to find various obsolete code usage
 * (deprecated and removed Magento 1 legacy methods, properties, classes, etc.)
 */
namespace Magento\Test\Legacy;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\Utility\AggregateInvoker;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Utility\ChangedFiles;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ObsoleteCodeTest extends \PHPUnit_Framework_TestCase
{
    /**@#+
     * Lists of obsolete entities from fixtures
     *
     * @var array
     */
    protected static $_classes = [];

    protected static $_constants = [];

    protected static $_methods = [];

    protected static $_attributes = [];

    protected static $_namespaces = [];

    protected static $_paths = [];

    /**#@-*/

    /**
     * Read fixtures into memory as arrays
     */
    public static function setUpBeforeClass()
    {
        $errors = [];
        self::_populateList(self::$_classes, $errors, 'obsolete_classes*.php', false);
        self::_populateList(self::$_constants, $errors, 'obsolete_constants*.php');
        self::_populateList(self::$_methods, $errors, 'obsolete_methods*.php');
        self::_populateList(self::$_paths, $errors, 'obsolete_paths*.php', false);
        self::_populateList(self::$_namespaces, $errors, 'obsolete_namespaces*.php', false);
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
            throw new \Exception($message);
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
            $readList = include $file;
            foreach ($readList as $row) {
                list($item, $scope, $replacement, $isDeprecated) = self::_padRow($row, $hasScope);
                $key = "{$item}|{$scope}";
                if (isset($list[$key])) {
                    $errors[$file][] = $key;
                } else {
                    $list[$key] = [$item, $scope, $replacement, $isDeprecated];
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
            return array_pad($row, 4, '');
        }
        list($item, $replacement) = array_pad($row, 2, '');
        return [$item, '', $replacement, ''];
    }

    public function testPhpFiles()
    {
        $invoker = new AggregateInvoker($this);
        $changedFiles = ChangedFiles::getPhpFiles(__DIR__ . '/../_files/changed_files*');
        $blacklistFiles = $this->getBlacklistFiles();
        foreach ($blacklistFiles as $blacklistFile) {
            unset($changedFiles[BP . $blacklistFile]);
        }
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                $this->_testObsoleteClasses($content);
                $this->_testObsoleteNamespaces($content);
                $this->_testObsoleteMethods($content, $file);
                $this->_testGetChildSpecialCase($content, $file);
                $this->_testGetOptionsSpecialCase($content);
                $this->_testObsoleteMethodArguments($content);
                $this->_testObsoleteProperties($content);
                $this->_testObsoleteActions($content);
                $this->_testObsoleteConstants($content);
                $this->_testObsoletePropertySkipCalculate($content);
            },
            $changedFiles
        );
    }

    public function testClassFiles()
    {
        $invoker = new AggregateInvoker($this);
        $invoker(
            function ($file) {
                $this->_testObsoletePaths($file);
            },
            Files::init()->getPhpFiles()
        );
    }

    public function testTemplateMageCalls()
    {
        $invoker = new AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                $this->_assertNotRegExp(
                    '/\bMage::(\w+?)\(/iS',
                    $content,
                    "Static Method of 'Mage' class is obsolete."
                );
            },
            Files::init()->getPhpFiles(
                Files::INCLUDE_TEMPLATES
                | Files::INCLUDE_TESTS
                | Files::AS_DATA_SET
            )
        );
    }

    public function testXmlFiles()
    {
        $invoker = new AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                $this->_testObsoleteClasses($content, $file);
                $this->_testObsoleteNamespaces($content);
                $this->_testObsoletePaths($file);
            },
            Files::init()->getXmlFiles()
        );
    }

    public function testJsFiles()
    {
        $invoker = new AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                $this->_testObsoletePropertySkipCalculate($content);
            },
            Files::init()->getJsFiles()
        );
    }

    /**
     * Assert that obsolete classes are not used in the content
     *
     * @param string $content
     */
    protected function _testObsoleteClasses($content)
    {
        /* avoid collision between obsolete class name and valid namespace */
        $content = preg_replace('/namespace[^;]+;/', '', $content);
        foreach (self::$_classes as $row) {
            list($class, , $replacement) = $row;
            $this->_assertNotRegExp(
                '/[^a-z\d_]' . preg_quote($class, '/') . '[^a-z\d_\\\\]/iS',
                $content,
                $this->_suggestReplacement(sprintf("Class '%s' is obsolete.", $class), $replacement)
            );
        }
    }

    /**
     * Assert that obsolete classes are not used in the content
     *
     * @param string $content
     */
    protected function _testObsoleteNamespaces($content)
    {
        foreach (self::$_namespaces as $row) {
            list($namespace, , $replacement) = $row;
            $this->_assertNotRegExp(
                '/namespace\s+' . preg_quote($namespace, '/') . ';/S',
                $content,
                $this->_suggestReplacement(sprintf("Namespace '%s' is obsolete.", $namespace), $replacement)
            );
            $this->_assertNotRegExp(
                '/[^a-zA-Z\d_]' . preg_quote($namespace . '\\', '/') . '/S',
                $content,
                $this->_suggestReplacement(sprintf("Namespace '%s' is obsolete.", $namespace), $replacement)
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
     * @param string $file
     */
    protected function _testObsoleteMethods($content, $file)
    {
        foreach (self::$_methods as $row) {
            list($method, $class, $replacement, $isDeprecated) = $row;
            $quotedMethod = preg_quote($method, '/');
            if ($class) {
                $message = $this->_suggestReplacement(
                    "Method '{$class}::{$method}()' is obsolete in file '{$file}'.",
                    $replacement
                );
                // without opening parentheses to match static callbacks notation
                $this->_assertNotRegExp(
                    '/' . preg_quote($class, '/') . '::\s*' . $quotedMethod . '[^a-z\d_]/iS',
                    $content,
                    $message
                );
                if ($this->_isClassOrInterface($content, $class) || $this->_isDirectDescendant($content, $class)) {
                    if (!$isDeprecated) {
                        $this->_assertNotRegExp('/function\s*' . $quotedMethod . '\s*\(/iS', $content, $message);
                    }
                    $this->_assertNotRegExp('/this->' . $quotedMethod . '\s*\(/iS', $content, $message);
                    $this->_assertNotRegExp(
                        '/(self|static|parent)::\s*' . $quotedMethod . '\s*\(/iS',
                        $content,
                        $message
                    );
                }
            } else {
                $message = $this->_suggestReplacement(
                    "Function or method '{$method}()' is obsolete in file '{$file}'.",
                    $replacement
                );
                $this->_assertNotRegExp(
                    '/(?<!public|protected|private|static)\s+function\s*' . $quotedMethod . '\s*\(/iS',
                    $content,
                    $message
                );
                $this->_assertNotRegExp(
                    '/(?<![a-z\d_:]|->|function\s)' . $quotedMethod . '\s*\(/iS',
                    $content,
                    $message
                );
            }
        }
    }

    /**
     * Assert that obsolete paths are not used in the content
     *
     * This method will search the content for references to class
     * that start with obsolete namespace
     *
     * @param string $file
     */
    protected function _testObsoletePaths($file)
    {
        foreach (self::$_paths as $row) {
            list($obsoletePath, , $replacementPath) = $row;
            $relativePath = str_replace(BP, '', $file);
            $message = $this->_suggestReplacement(
                "Path '{$obsoletePath}' is obsolete.",
                $replacementPath
            );
            $this->assertStringStartsNotWith($obsoletePath . '/', $relativePath, $message);
            $this->assertStringStartsNotWith($obsoletePath . '.', $relativePath, $message);
            $this->assertStringStartsNotWith($obsoletePath . 'Factory.', $relativePath, $message);
            $this->assertStringStartsNotWith($obsoletePath . 'Interface.', $relativePath, $message);
            $this->assertStringStartsNotWith($obsoletePath . 'Test.', $relativePath, $message);
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
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            if (0 === strpos($file, $modulePath)) {
                $this->_assertNotRegexp(
                    '/[^a-z\d_]getChild\s*\(/iS',
                    $content,
                    'Block method getChild() is obsolete. ' .
                    'Replacement suggestion: \Magento\Framework\View\Element\AbstractBlock::getChildBlock()'
                );
            }
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
            '/getOptions\(\)\s*->get(Base|App|Code|Design|Etc|Lib|Locale|Js|Media' .
            '|Var|Tmp|Cache|Log|Session|Upload|Export)?Dir\(/S',
            $content,
            'The class \Magento\Core\Model\Config\Options is obsolete. '
            . 'Replacement suggestion: \Magento\Framework\Filesystem'
        );
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteMethodArguments($content)
    {
        $this->_assertNotRegExp(
            '/[^a-z\d_]getTypeInstance\s*\(\s*[^\)]+/iS',
            $content,
            'Backwards-incompatible change: method getTypeInstance() is not supposed to be invoked with any arguments.'
        );
        $this->_assertNotRegExp(
            '/\->getUsedProductIds\(([^\)]+,\s*[^\)]+)?\)/',
            $content,
            'Backwards-incompatible change: method getUsedProductIds($product)' .
            ' must be invoked with one and only one argument - product model object'
        );

        $this->_assertNotRegExp(
            '#->_setActiveMenu\([\'"]([\w\d/_]+)[\'"]\)#Ui',
            $content,
            'Backwards-incompatible change: method _setActiveMenu()' .
            ' must be invoked with menu item identifier than xpath for menu item'
        );
    }

    /**
     * @param string $content
     */
    protected function _testObsoleteProperties($content)
    {
        foreach (self::$_attributes as $row) {
            list($attribute, $class, $replacement) = $row;
            if ($class) {
                if (!$this->_isClassOrInterface($content, $class) && !$this->_isDirectDescendant($content, $class)) {
                    continue;
                }
                $fullyQualified = "{$class}::\${$attribute}";
            } else {
                $fullyQualified = $attribute;
            }
            $this->_assertNotRegExp(
                '/[^a-z\d_]' . preg_quote($attribute, '/') . '[^a-z\d_]/iS',
                $content,
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
        $this->_assertNotRegExp(
            '#[^a-z\d_/]catalog/product/image[^a-z\d_/]#iS',
            $content,
            "Action 'catalog/product/image' is obsolete. {$suggestion}"
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
                $class = ltrim($class, '\\');
                $this->_checkConstantWithFullClasspath($constant, $class, $replacement, $content);
                $this->_checkConstantWithClasspath($constant, $class, $replacement, $content);
            } else {
                $regex = '\b' . preg_quote($constant, '/') . '\b';
                $this->_checkExistenceOfObsoleteConstants($regex, '', $content, $constant, $replacement, $class);
            }
        }
    }

    /**
     * Build regular expression from Obsolete Constants with correspond to contents
     *
     * @param string $classPartialPath
     * @param string $content
     * @param string $constant
     * @return string
     */
    private function buildRegExFromObsoleteConstant($classPartialPath, $content, $constant)
    {
        $regex = preg_quote("{$classPartialPath}::{$constant}");
        if ($this->_isClassOrInterface($content, $classPartialPath)) {
            $regex .= '|' . $this->_getClassConstantDefinitionRegExp($constant)
                . '|' . preg_quote("self::{$constant}", '/')
                . '|' . preg_quote("static::{$constant}", '/');
        } elseif ($this->_isDirectDescendant($content, $classPartialPath)) {
            $regex .= '|' . preg_quote("parent::{$constant}", '/');
            if (!$this->_isClassConstantDefined($content, $constant)) {
                $regex .= '|' . preg_quote("self::{$constant}", '/') . '|' . preg_quote("static::{$constant}", '/');
            }
        }
        return $regex;
    }

    /**
     * Checks condition of using full classpath in 'use' with 'as' (Example: 'use A\B\C as D')
     * where A\B\C is the class where the constant is obsolete
     *
     * @param string $constant
     * @param string $class
     * @param string $replacement
     * @param string $content
     */
    private function _checkConstantWithFullClasspath($constant, $class, $replacement, $content)
    {
        $constantRegex = preg_quote($constant, '/');
        $classRegex = preg_quote($class);
        $this->_checkExistenceOfObsoleteConstants(
            $constantRegex,
            $classRegex,
            $content,
            "{$class}::{$constant}",
            $replacement,
            $class
        );
    }

    /**
     * Check all combinations of classpath with constant
     *
     * @param string $constant
     * @param string $class
     * @param string $replacement
     * @param string $content
     */
    private function _checkConstantWithClasspath($constant, $class, $replacement, $content)
    {
        $classPathParts = explode('\\', $class);
        $classPartialPath = '';
        for ($i = count($classPathParts) - 1; $i >= 0; $i--) {
            if ($i === (count($classPathParts) - 1)) {
                $classPartialPath = $classPathParts[$i] . $classPartialPath;
            } else {
                $classPartialPath = $classPathParts[$i] . '\\' . $classPartialPath;
            }
            $constantRegex = $this->buildRegExFromObsoleteConstant($classPartialPath, $content, $constant);
            $regexClassPartialPath = preg_replace('/' . preg_quote($classPartialPath) . '$/', '', $class);
            $classRegex = preg_quote($regexClassPartialPath . $classPathParts[$i]);
            if ($regexClassPartialPath !== '') {
                $classRegex .= '|' . preg_quote(rtrim($regexClassPartialPath, '\\'));
            }
            // Checks condition when classpath is distributed over namespace and class definition
            $classRegexNamespaceClass = '/namespace\s+' . preg_quote('\\') . '?(' . $classRegex . ')(\s|;)(\r?\n)+'
                . 'class\s+' . preg_quote('\\') . '?(' . preg_quote(rtrim($classPartialPath, '\\')) . ')\s*/';
            $matchNamespaceClass = preg_match($classRegexNamespaceClass, $content);
            $constantRegexPartial = '/\b(?P<classWithConst>([a-zA-Z0-9_' . preg_quote('\\') . ']*))('
                . preg_quote('::') . ')*' . '(' . preg_quote($constant, '/') . '\b)(\s*|;)/';
            $matchConstantPartial = preg_match($constantRegexPartial, $content, $match);
            if (($matchNamespaceClass === 1) && ($matchConstantPartial === 1) && ($match['classWithConst'] === '')) {
                $this->assertSame(
                    0,
                    1,
                    $this->_suggestReplacement(sprintf("Constant '%s' is obsolete.", $constant), $replacement)
                );
            } else {
                $this->_checkExistenceOfObsoleteConstants(
                    $constantRegex,
                    $classRegex,
                    $content,
                    "{$classPartialPath}::{$constant}",
                    $replacement,
                    $class
                );
            }
        }
    }

    /**
     * Check existence of Obsolete Constant in current content
     *
     * @param string $constantRegex
     * @param string $classRegex
     * @param string $content
     * @param string $constant
     * @param string $replacement
     * @param string $class
     */
    private function _checkExistenceOfObsoleteConstants(
        $constantRegex,
        $classRegex,
        $content,
        $constant,
        $replacement,
        $class
    ) {
        $constantRegexFull = '/\b(?P<constPart>((?P<classWithConst>([a-zA-Z0-9_' . preg_quote('\\') . ']*))('
            . preg_quote('::') . ')*' . '(' . $constantRegex . '\b)))(\s*|;)/';
        $matchConstant = preg_match_all($constantRegexFull, $content, $matchConstantString);
        $result = 0;
        if ($matchConstant === 1) {
            if ($classRegex !== '') {
                $classRegexFull = '/(?P<useOrNamespace>(use|namespace))\s+(?P<classPath>(' . preg_quote('\\')
                    . '?(' . $classRegex . ')))(\s+as\s+(?P<classAlias>([\w\d_]+)))?(\s|;)/';
                $matchClass = preg_match($classRegexFull, $content, $matchClassString);
                if ($matchClass === 1) {
                    if ($matchClassString['classAlias']) {
                        $result = $this->_checkAliasUseNamespace(
                            $constantRegex,
                            $matchConstantString,
                            $matchClassString,
                            $class
                        );
                    } else {
                        $result = $this->_checkNoAliasUseNamespace($matchConstantString, $matchClassString, $class);
                    }
                } else {
                    foreach ($matchConstantString['classWithConst'] as $constantMatch) {
                        if (trim($constantMatch, '\\') === $class) {
                            $result = 1;
                            break;
                        }
                    }

                }
            } else {
                $result = 1;
            }
        }
        $this->assertSame(
            0,
            $result,
            $this->_suggestReplacement(sprintf("Constant '%s' is obsolete.", $constant), $replacement)
        );
    }

    /**
     * Check proper usage of 'as' alias in 'use' or 'namespace' in context of constant
     *
     * @param string $constantRegex
     * @param string $matchConstantString
     * @param string $matchClassString
     * @param string $class
     * @return int
     */
    private function _checkAliasUseNamespace(
        $constantRegex,
        $matchConstantString,
        $matchClassString,
        $class
    ) {
        $foundProperUse = false;
        $foundAsComponent = false;
        $asComponent = $matchClassString['classAlias'];
        foreach ($matchConstantString['constPart'] as $constantMatch) {
            $expectedOnlyConst = '/' . $asComponent . preg_quote('::') . $constantRegex . '/';
            $expectedConstPartialClass = '/' . $asComponent . preg_quote('\\')
                . $constantRegex . '/';
            if ((preg_match($expectedOnlyConst, $constantMatch) === 1)
                || (preg_match($expectedConstPartialClass, $constantMatch) === 1)) {
                $foundAsComponent = true;
            }
            if (strpos($constantMatch, '::') !== false) {
                $foundProperUse = $this->_checkCompletePathOfClass(
                    $constantMatch,
                    $matchClassString,
                    $class,
                    $foundAsComponent,
                    $asComponent
                );
                if ($foundProperUse) {
                    break;
                }
            }
        }
        if ($foundProperUse) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Check proper usage of classpath in constant and 'use'/'namespace' when there is no 'as' alias
     *
     * @param string $matchConstantString
     * @param string $matchClassString
     * @param string $class
     * @return int
     */
    private function _checkNoAliasUseNamespace(
        $matchConstantString,
        $matchClassString,
        $class
    ) {
        $foundProperUse = false;
        foreach ($matchConstantString['constPart'] as $constantMatch) {
            $foundProperUse = $this->_checkCompletePathOfClass(
                $constantMatch,
                $matchClassString,
                $class
            );
            if ($foundProperUse) {
                break;
            }
        }
        if ($foundProperUse) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Check if class path with constant and in 'use' or 'namespace' forms complete classpath
     *
     * @param string $constantMatch
     * @param array $matchClassString
     * @param string $class
     * @param bool $foundAsComponent
     * @param string $asComponent
     * @return bool
     */
    private function _checkCompletePathOfClass(
        $constantMatch,
        $matchClassString,
        $class,
        $foundAsComponent = false,
        $asComponent = ''
    ) {
        $temp = explode('::', $constantMatch);
        $pathWithConst = trim(ltrim(str_replace('\\\\', '\\', $temp[0]), '\\'));
        if ($pathWithConst === $class) {
            return true;
        }
        if ($foundAsComponent) {
            $pathWithConst = ltrim(preg_replace('/^' . $asComponent . '/', '', $pathWithConst), '\\');
            if ($pathWithConst === '') {
                return true;
            }
        }
        $pathWithConstParts = explode('\\', $pathWithConst);
        $pathInUseNamespace = trim($matchClassString['classPath'], '\\');
        $pathInUseNamespaceTruncated = trim(trim(
            preg_replace(
                '/' . preg_quote($pathWithConstParts[0]) . '$/',
                '',
                $pathInUseNamespace
            ),
            '\\'
        ));
        if ($this->_checkClasspathProperDivisionNoConstantPath(
            $pathInUseNamespaceTruncated,
            $pathInUseNamespace,
            $matchClassString,
            $class,
            $foundAsComponent
        )) {
            return true;
        } else {
            return $this->_checkClasspathProperDivisionWithConstantPath(
                $pathInUseNamespaceTruncated,
                $pathInUseNamespace,
                $pathWithConst,
                $class,
                $foundAsComponent
            );
        }
    }

    /**
     * Check if classpath is divided in two places with correct constant name
     *
     * @param string $pathInUseNamespaceTruncated
     * @param string $pathInUseNamespace
     * @param array $matchClassString
     * @param string $class
     * @param bool $foundAsComponent
     * @return bool
     */
    private function _checkClasspathProperDivisionNoConstantPath(
        $pathInUseNamespaceTruncated,
        $pathInUseNamespace,
        $matchClassString,
        $class,
        $foundAsComponent
    ) {
        if ($pathInUseNamespaceTruncated === $pathInUseNamespace && $pathInUseNamespaceTruncated !== $class
            && ($foundAsComponent || (strpos($matchClassString['useOrNamespace'], 'namespace') !== false))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if classpath is divided in two places with constant properly with or without alias
     *
     * @param string $pathInUseNamespaceTruncated
     * @param string $pathInUseNamespace
     * @param string $pathWithConst
     * @param string $class
     * @param bool $foundAsComponent
     * @return bool
     */
    private function _checkClasspathProperDivisionWithConstantPath(
        $pathInUseNamespaceTruncated,
        $pathInUseNamespace,
        $pathWithConst,
        $class,
        $foundAsComponent
    ) {
        if ((($pathInUseNamespaceTruncated . '\\' . $pathWithConst === $class)
                && ($pathInUseNamespaceTruncated !== $pathInUseNamespace) && !$foundAsComponent)
            || (($pathInUseNamespaceTruncated === $class) && (strpos($pathWithConst, '\\') === false)
                && $foundAsComponent)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether a class constant is defined in the content or not
     *
     * @param string $content
     * @param string $constant
     * @return bool
     */
    protected function _isClassConstantDefined($content, $constant)
    {
        return (bool)preg_match('/' . $this->_getClassConstantDefinitionRegExp($constant) . '/S', $content);
    }

    /**
     * Retrieve a PCRE matching a class constant definition
     *
     * @param string $constant
     * @return string
     */
    protected function _getClassConstantDefinitionRegExp($constant)
    {
        return '\bconst\s+' . preg_quote($constant, '/') . '\b';
    }

    /**
     * @param string $content
     */
    protected function _testObsoletePropertySkipCalculate($content)
    {
        $this->_assertNotRegExp(
            '/[^a-z\d_]skipCalculate[^a-z\d_]/iS',
            $content,
            "Configuration property 'skipCalculate' is obsolete."
        );
    }

    /**
     * Analyze contents to determine whether this is declaration of specified class/interface
     *
     * @param string $content
     * @param string $name
     * @return bool
     */
    protected function _isClassOrInterface($content, $name)
    {
        $name = preg_quote($name, '/');
        return (bool)preg_match('/\b(?:class|interface)\s+' . $name . '\b[^{]*\{/iS', $content);
    }

    /**
     * Analyze contents to determine whether this is a direct descendant of specified class/interface
     *
     * @param string $content
     * @param string $name
     * @return bool
     */
    protected function _isDirectDescendant($content, $name)
    {
        $name = preg_quote($name, '/');
        return (bool)preg_match(
            '/\s+extends\s+' . $name . '\b|\s+implements\s+[^{]*\b' . $name . '\b[^{^\\\\]*\{/iS',
            $content
        );
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

    public function testMageMethodsObsolete()
    {
        $ignored = $this->getBlacklistFiles(true);
        $files = Files::init()->getPhpFiles(
            Files::INCLUDE_APP_CODE
            | Files::INCLUDE_TESTS
            | Files::INCLUDE_DEV_TOOLS
            | Files::INCLUDE_LIBS
        );
        $files = array_map('realpath', $files);
        $files = array_diff($files, $ignored);
        $files = Files::composeDataSets($files);

        $invoker = new AggregateInvoker($this);
        $invoker(
            /**
             * Check absence of obsolete Mage class usages
             *
             * @param string $file
             */
            function ($file) {
                $this->_assertNotRegExp(
                    '/[^a-z\d_]Mage\s*::/i',
                    file_get_contents($file),
                    '"Mage" class methods are obsolete'
                );
            },
            $files
        );
    }

    /**
     * @param string $appPath
     * @param string $pattern
     * @return array
     * @throws \Exception
     */
    private function processPattern($appPath, $pattern)
    {
        $files = [];
        $relativePathStart = strlen($appPath);

        $fileSet = glob($appPath . DIRECTORY_SEPARATOR . $pattern, GLOB_NOSORT);
        foreach ($fileSet as $file) {
            $files[] = substr($file, $relativePathStart);
        }

        return $files;
    }

    /**
     * Reads list of blacklisted files
     *
     * @param bool $absolutePath
     * @return array
     * @throws \Exception
     */
    private function getBlacklistFiles($absolutePath = false)
    {
        $blackList = include __DIR__ . '/_files/blacklist/obsolete_mage.php';
        $ignored = [];
        $appPath = BP;
        foreach ($blackList as $file) {
            if ($absolutePath) {
                $ignored = array_merge($ignored, glob($appPath . DIRECTORY_SEPARATOR . $file, GLOB_NOSORT));
            } else {
                $ignored = array_merge($ignored, $this->processPattern($appPath, $file));
            }
        }
        return $ignored;
    }
}
