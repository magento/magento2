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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Di\Code\Scanner;

use Magento\Tools\Di\Compiler\Log\Log;

class PhpScanner implements ScannerInterface
{
    /**
     * @var Log $log
     */
    protected $_log;

    /**
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->_log = $log;
    }

    /**
     * Fetch factories from class constructor
     *
     * @param $file string
     * @param $reflectionClass mixed
     * @return array
     */
    protected function _fetchFactories($file, $reflectionClass)
    {
        $absentFactories = array();
        if ($reflectionClass->hasMethod('__construct')) {
            $constructor = $reflectionClass->getMethod('__construct');
            $parameters = $constructor->getParameters();
            /** @var $parameter \ReflectionParameter */
            foreach ($parameters as $parameter) {
                preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', $parameter->__toString(), $matches);
                if (isset($matches[1]) && substr($matches[1], -7) == 'Factory') {
                    $factoryClassName = $matches[1];
                    if (class_exists($factoryClassName)) {
                        continue;
                    }
                    $entityName = rtrim(substr($factoryClassName, 0, -7), '\\');
                    if (!class_exists($entityName)) {
                        $this->_log->add(
                            Log::CONFIGURATION_ERROR,
                            $factoryClassName,
                            'Invalid Factory for nonexistent class ' . $entityName . ' in file ' . $file
                        );
                        continue;
                    }

                    if (substr($factoryClassName, -8) == '\\Factory') {
                        $this->_log->add(
                            Log::CONFIGURATION_ERROR,
                            $factoryClassName,
                            'Invalid Factory declaration for class ' . $entityName . ' in file ' . $file
                        );
                        continue;
                    }
                    $absentFactories[] = $factoryClassName;
                }
            }
        }
        return $absentFactories;
    }

    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $output = array();
        foreach ($files as $file) {
            $classes = $this->_getDeclaredClasses($file);
            foreach ($classes as $className) {
                $reflectionClass = new \ReflectionClass($className);
                $absentFactories = $this->_fetchFactories($file, $reflectionClass);
                if (!empty($absentFactories)) {
                    $output = array_merge($output, $absentFactories);
                }
            }
        }
        return array_unique($output);
    }

    /**
     * @param $tokenIterator int
     * @param $count int
     * @param $tokens array
     * @return string
     */
    protected function _fetchNamespace($tokenIterator, $count, $tokens)
    {
        $namespace = '';
        for ($tokenOffset = $tokenIterator + 1; $tokenOffset < $count; ++$tokenOffset) {
            if ($tokens[$tokenOffset][0] === T_STRING) {
                $namespace .= "\\" . $tokens[$tokenOffset][1];
            } elseif ($tokens[$tokenOffset] === '{' || $tokens[$tokenOffset] === ';') {
                break;
            }
        }
        return $namespace;
    }

    /**
     * @param $namespace string
     * @param $tokenIterator int
     * @param $count int
     * @param $tokens array
     * @return array
     */
    protected function _fetchClasses($namespace, $tokenIterator, $count, $tokens)
    {
        $classes = array();
        for ($tokenOffset = $tokenIterator + 1; $tokenOffset < $count; ++$tokenOffset) {
            if ($tokens[$tokenOffset] === '{') {
                $classes[] = $namespace . "\\" . $tokens[$tokenIterator + 2][1];
            }
        }
        return $classes;
    }

    /**
     * Get classes declared in the file
     *
     * @param string $file
     * @return array
     */
    protected function _getDeclaredClasses($file)
    {
        $classes = array();
        $namespace = "";
        $tokens = token_get_all(file_get_contents($file));
        $count = count($tokens);

        for ($tokenIterator = 0; $tokenIterator < $count; $tokenIterator++) {
            if ($tokens[$tokenIterator][0] === T_NAMESPACE) {
                $namespace .= $this->_fetchNamespace($tokenIterator, $count, $tokens);
            }

            if ($tokens[$tokenIterator][0] === T_CLASS) {
                $classes = array_merge($classes, $this->_fetchClasses($namespace, $tokenIterator, $count, $tokens));
            }
        }
        return array_unique($classes);
    }
}
