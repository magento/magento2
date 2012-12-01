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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the @magentoAppIsolation DocBlock annotation
 */
class Magento_Test_Annotation_AppIsolation
{
    /**
     * Flag to prevent an excessive test case isolation if the last test has been just isolated
     *
     * @var bool
     */
    private $_hasNonIsolatedTests = true;

    /**
     * Should clearStaticVariables() be invoked in endTestSuite()
     *
     * @var bool
     */
    protected $_runClearStatics = false;

    /**
     * Directories to clear static variables
     *
     * @var array
     */
    protected static $_cleanableFolders = array(
        '/app/code/',
        '/dev/tests/',
        '/lib/',
    );

    /**
     * Classes to exclude from static variables cleaning
     *
     * @var array
     */
    protected static $_classesToSkip = array(
        'Mage',
        'Magento_Test_Bootstrap',
        'Magento_Test_Event_Magento',
        'Magento_Test_Event_PhpUnit',
        'Magento_Test_Annotation_AppIsolation',
    );

    /**
     * Check whether it is allowed to clean given class static variables
     *
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    protected static function _isClassCleanable(ReflectionClass $reflectionClass)
    {
        // 1. do not process php internal classes
        if ($reflectionClass->isInternal()) {
            return false;
        }

        // 2. do not process blacklisted classes from integration framework
        foreach (self::$_classesToSkip as $notCleanableClass) {
            if ($reflectionClass->getName() == $notCleanableClass
                || is_subclass_of($reflectionClass->getName(), $notCleanableClass)
            ) {
                return false;
            }
        }

        // 3. process only files from specific folders
        $fileName = $reflectionClass->getFileName();

        if ($fileName) {
            $fileName = str_replace('\\', '/', $fileName);
            foreach (self::$_cleanableFolders as $directory) {
                if (stripos($fileName, $directory) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Clear static variables (after running controller test case)
     * @TODO: workaround to reduce memory leak
     * @TODO: refactor all code where objects are stored to static variables to use object manager instead
     */
    public static function clearStaticVariables()
    {
        $classes = get_declared_classes();

        foreach ($classes as $class) {
            $reflectionCLass = new ReflectionClass($class);
            if (self::_isClassCleanable($reflectionCLass)) {
                $staticProperties = $reflectionCLass->getProperties(ReflectionProperty::IS_STATIC);
                foreach ($staticProperties as $staticProperty) {
                    $staticProperty->setAccessible(true);
                    $value = $staticProperty->getValue();
                    if (is_object($value) || (is_array($value) && is_object(current($value)))) {
                        $staticProperty->setValue(null);
                    }
                    unset($value);
                }
            }
        }
    }

    /**
     * Isolate global application objects
     */
    protected function _isolateApp()
    {
        if ($this->_hasNonIsolatedTests) {
            $this->_cleanupCache();
            Magento_Test_Bootstrap::getInstance()->reinitialize();
            $this->_hasNonIsolatedTests = false;
        }
    }

    /**
     * Remove cache polluted by other tests excluding performance critical cache (configuration, ddl)
     */
    protected function _cleanupCache()
    {
        Mage::app()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
            array(Mage_Core_Model_Config::CACHE_TAG,
                Varien_Db_Adapter_Pdo_Mysql::DDL_CACHE_TAG,
                'DB_PDO_MSSQL_DDL', // Varien_Db_Adapter_Pdo_Mssql::DDL_CACHE_TAG
                'DB_ORACLE_DDL', // Varien_Db_Adapter_Oracle::DDL_CACHE_TAG
            )
        );
    }

    /**
     * Isolate application before running test case
     */
    public function startTestSuite()
    {
        $this->_isolateApp();
    }

    /**
     * Handler for 'endTest' event
     *
     * @param PHPUnit_Framework_TestCase $test
     * @throws Magento_Exception
     */
    public function endTest(PHPUnit_Framework_TestCase $test)
    {
        $this->_hasNonIsolatedTests = true;

        /* Determine an isolation from doc comment */
        $annotations = $test->getAnnotations();
        if (isset($annotations['method']['magentoAppIsolation'])) {
            $isolation = $annotations['method']['magentoAppIsolation'];
            if ($isolation !== array('enabled') && $isolation !== array('disabled')) {
                throw new Magento_Exception(
                    'Invalid "@magentoAppIsolation" annotation, can be "enabled" or "disabled" only.'
                );
            }
            $isIsolationEnabled = $isolation === array('enabled');
        } else {
            if ($test instanceof Magento_Test_TestCase_ControllerAbstract) {
                $this->_runClearStatics = true;
                /* Controller tests should be isolated by default */
                $isIsolationEnabled = true;
            } else {
                $isIsolationEnabled = false;
            }
        }

        if ($isIsolationEnabled) {
            $this->_isolateApp();
        }
    }

    /**
     * Clear static cache
     */
    public function endTestSuite()
    {
        if ($this->_runClearStatics) {
            self::clearStaticVariables();
            // forced garbage collection to avoid process non-zero exit code (exec returned: 139) caused by PHP bug
            gc_collect_cycles();

            $this->_runClearStatics = false;
        }
    }
}
