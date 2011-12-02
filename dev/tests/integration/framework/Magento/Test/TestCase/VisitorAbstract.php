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
 * @package     Test
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * An ancestor class for tests, that visit many files, and where more visiting method can be added with time
 */
abstract class Magento_Test_TestCase_VisitorAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * List of methods in this class, that are designed to check file content.
     * Filled automatically via reflection.
     *
     * @var array
     */
    protected $_visitorMethods = null;

    /**
     * Returns all methods in this class, that are designed to visit the file content.
     * Protected methods starting with '_visit' are considered to be visitor methods.
     *
     * @return array
     */
    protected function _getVisitorMethods()
    {
        if ($this->_visitorMethods === null) {
            $this->_visitorMethods = array();
            $reflection = new ReflectionClass($this);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
                if (substr($method->name, 0, 6) == '_visit') {
                    $this->_visitorMethods[] = $method->name;
                }
            }
        }

        return $this->_visitorMethods;
    }

    /**
     * Checks whether file path has required extension
     *
     * @param string|array $extensions
     * @return bool
     */
    protected function _fileHasExtensions($fileInfo, $extensions)
    {
        if (is_string($extensions)) {
            $extensions = array($extensions);
        }

        $fileExtension = pathinfo($fileInfo->getBasename(), PATHINFO_EXTENSION);
        $key = array_search($fileExtension, $extensions);
        return ($key !== false);
    }
}
