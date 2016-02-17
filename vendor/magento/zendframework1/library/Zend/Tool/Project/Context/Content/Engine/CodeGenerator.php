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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Content_Engine_CodeGenerator
{
    /**
     * @var Zend_Tool_Framework_Client_Storage
     */
    protected $_storage = null;

    /**
     * @var string
     */
    protected $_contentPrefix = null;

    /**
     * __construct()
     *
     * @param Zend_Tool_Framework_Client_Storage $storage
     * @param string $contentPrefix
     */
    public function __construct(Zend_Tool_Framework_Client_Storage $storage, $contentPrefix)
    {
        $this->_storage       = $storage;
        $this->_contentPrefix = $contentPrefix;
    }

    /**
     * hasContent()
     *
     * @param Zend_Tool_Project_Context_Interface $context
     * @param string $method
     * @return string
     */
    public function hasContent(Zend_Tool_Project_Context_Interface $context, $method)
    {
        return $this->_storage->has($this->_contentPrefix . '/' . $context->getName() . '/' . $method . '.php');
    }

    /**
     * getContent()
     *
     * @param Zend_Tool_Project_Context_Interface $context
     * @param string $method
     * @param mixed $parameters
     * @return string
     */
    public function getContent(Zend_Tool_Project_Context_Interface $context, $method, $parameters)
    {
        $streamUri = $this->_storage->getStreamUri($this->_contentPrefix . '/' . $context->getName() . '/' . $method . '.php');

        if (method_exists($context, 'getCodeGenerator')) {
            $codeGenerator = $context->getCodeGenerator();
        } else {
            $codeGenerator = new Zend_CodeGenerator_Php_File();
        }

        $codeGenerator = include $streamUri;

        if (!$codeGenerator instanceof Zend_CodeGenerator_Abstract) {
            throw new Zend_Tool_Project_Exception('Custom file at ' . $streamUri . ' did not return the $codeGenerator object.');
        }

        return $codeGenerator->generate();
    }


}
