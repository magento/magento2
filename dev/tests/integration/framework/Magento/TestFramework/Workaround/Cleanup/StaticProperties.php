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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Workaround for decreasing memory consumption by cleaning up static properties
 */
namespace Magento\TestFramework\Workaround\Cleanup;

class StaticProperties
{
    /**
     * Directories to clear static variables
     *
     * @var array
     */
    protected static $_cleanableFolders = array('/app/code/', '/dev/tests/', '/lib/internal/');

    /**
     * Classes to exclude from static variables cleaning
     *
     * @var array
     */
    protected static $_classesToSkip = array(
        'Mage',
        'Magento\Framework\App\ObjectManager',
        'Magento\TestFramework\Helper\Bootstrap',
        'Magento\TestFramework\Event\Magento',
        'Magento\TestFramework\Event\PhpUnit',
        'Magento\TestFramework\Annotation\AppIsolation',
        'Magento\Framework\Phrase'
    );

    /**
     * Check whether it is allowed to clean given class static variables
     *
     * @param \ReflectionClass $reflectionClass
     * @return bool
     */
    protected static function _isClassCleanable(\ReflectionClass $reflectionClass)
    {
        // 1. do not process php internal classes
        if ($reflectionClass->isInternal()) {
            return false;
        }

        // 2. do not process blacklisted classes from integration framework
        foreach (self::$_classesToSkip as $notCleanableClass) {
            if ($reflectionClass->getName() == $notCleanableClass || is_subclass_of(
                $reflectionClass->getName(),
                $notCleanableClass
            )
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
     * @TODO: refactor all code where objects are stored to static variables to use object manager instead
     */
    public static function clearStaticVariables()
    {
        $classes = get_declared_classes();

        foreach ($classes as $class) {
            $reflectionCLass = new \ReflectionClass($class);
            if (self::_isClassCleanable($reflectionCLass)) {
                $staticProperties = $reflectionCLass->getProperties(\ReflectionProperty::IS_STATIC);
                foreach ($staticProperties as $staticProperty) {
                    $staticProperty->setAccessible(true);
                    $value = $staticProperty->getValue();
                    if (is_object($value) || is_array($value) && is_object(current($value))) {
                        $staticProperty->setValue(null);
                    }
                    unset($value);
                }
            }
        }
    }

    /**
     * Handler for 'endTestSuite' event
     *
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $clearStatics = false;
        foreach ($suite->tests() as $test) {
            if ($test instanceof \Magento\TestFramework\TestCase\AbstractController) {
                $clearStatics = true;
                break;
            }
        }
        if ($clearStatics) {
            self::clearStaticVariables();
        }
    }
}
