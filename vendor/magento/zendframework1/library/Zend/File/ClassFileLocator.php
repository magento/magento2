<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_File
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/File/PhpClassFile.php';

/**
 * Locate files containing PHP classes, interfaces, or abstracts
 *
 * @package    Zend_File
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://framework.zend.com/license/new-bsd}
 */
class Zend_File_ClassFileLocator extends FilterIterator
{
    /**
     * Create an instance of the locator iterator
     *
     * Expects either a directory, or a DirectoryIterator (or its recursive variant)
     * instance.
     *
     * @param  string|DirectoryIterator $dirOrIterator
     */
    public function __construct($dirOrIterator = '.')
    {
        if (is_string($dirOrIterator)) {
            if (!is_dir($dirOrIterator)) {
                throw new InvalidArgumentException('Expected a valid directory name');
            }

            $dirOrIterator = new RecursiveDirectoryIterator($dirOrIterator);
        }
        if (!$dirOrIterator instanceof DirectoryIterator) {
            throw new InvalidArgumentException('Expected a DirectoryIterator');
        }

        if ($dirOrIterator instanceof RecursiveIterator) {
            $iterator = new RecursiveIteratorIterator($dirOrIterator);
        } else {
            $iterator = $dirOrIterator;
        }

        parent::__construct($iterator);
        $this->setInfoClass('Zend_File_PhpClassFile');

        // Forward-compat with PHP 5.3
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (!defined('T_NAMESPACE')) {
                define('T_NAMESPACE', 'namespace');
            }
            if (!defined('T_NS_SEPARATOR')) {
                define('T_NS_SEPARATOR', '\\');
            }
        }
    }

    /**
     * Filter for files containing PHP classes, interfaces, or abstracts
     *
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();
        // If we somehow have something other than an SplFileInfo object, just
        // return false
        if (!$file instanceof SplFileInfo) {
            return false;
        }

        // If we have a directory, it's not a file, so return false
        if (!$file->isFile()) {
            return false;
        }

        // If not a PHP file, skip
        if ($file->getBasename('.php') == $file->getBasename()) {
            return false;
        }

        $contents = file_get_contents($file->getRealPath());
        $tokens   = token_get_all($contents);
        $count    = count($tokens);
        $t_trait  = defined('T_TRAIT') ? T_TRAIT : -1; // For preserve PHP 5.3 compatibility
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                // single character token found; skip
                $i++;
                continue;
            }
            switch ($token[0]) {
                case T_NAMESPACE:
                    // Namespace found; grab it for later
                    $namespace = '';
                    for ($i++; $i < $count; $i++) {
                        $token = $tokens[$i];
                        if (is_string($token)) {
                            if (';' === $token) {
                                $saveNamespace = false;
                                break;
                            }
                            if ('{' === $token) {
                                $saveNamespace = true;
                                break;
                            }
                            continue;
                        }
                        list($type, $content, $line) = $token;
                        switch ($type) {
                            case T_STRING:
                            case T_NS_SEPARATOR:
                                $namespace .= $content;
                                break;
                        }
                    }
                    if ($saveNamespace) {
                        $savedNamespace = $namespace;
                    }
                    break;
                case $t_trait:
                case T_CLASS:
                case T_INTERFACE:
                    // Abstract class, class, interface or trait found

                    // Get the classname
                    for ($i++; $i < $count; $i++) {
                        $token = $tokens[$i];
                        if (is_string($token)) {
                            continue;
                        }
                        list($type, $content, $line) = $token;
                        if (T_STRING == $type) {
                    // If a classname was found, set it in the object, and
                    // return boolean true (found)
                            if (!isset($namespace) || null === $namespace) {
                                if (isset($saveNamespace) && $saveNamespace) {
                                    $namespace = $savedNamespace;
                                } else {
                                    $namespace = null;
                    }

                            }
                            $class = (null === $namespace) ? $content : $namespace . '\\' . $content;
                            $file->addClass($class);
                            $namespace = null;
                    break;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $classes = $file->getClasses();
        if (!empty($classes)) {
            return true;
        }
        // No class-type tokens found; return false
        return false;
    }
}
