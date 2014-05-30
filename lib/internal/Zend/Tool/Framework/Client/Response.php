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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Response.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Response
{
    /**
     * @var callback|null
     */
    protected $_callback = null;

    /**
     * @var array
     */
    protected $_content = array();

    /**
     * @var Zend_Tool_Framework_Exception
     */
    protected $_exception = null;

    /**
     * @var array
     */
    protected $_decorators = array();

    /**
     * @var array
     */
    protected $_defaultDecoratorOptions = array();

    /**
     * setContentCallback()
     *
     * @param callback $callback
     * @return Zend_Tool_Framework_Client_Response
     */
    public function setContentCallback($callback)
    {
        if (!is_callable($callback)) {
            #require_once 'Zend/Tool/Framework/Client/Exception.php';
            throw new Zend_Tool_Framework_Client_Exception('The callback provided is not callable');
        }
        $this->_callback = $callback;
        return $this;
    }

    /**
     * setContent()
     *
     * @param string $content
     * @return Zend_Tool_Framework_Client_Response
     */
    public function setContent($content, Array $decoratorOptions = array())
    {
        $this->_applyDecorators($content, $decoratorOptions);

        $this->_content = array();
        $this->appendContent($content);
        return $this;
    }

    /**
     * appendCallback
     *
     * @param string $content
     * @return Zend_Tool_Framework_Client_Response
     */
    public function appendContent($content, Array $decoratorOptions = array())
    {
        $content = $this->_applyDecorators($content, $decoratorOptions);

        if ($this->_callback !== null) {
            call_user_func($this->_callback, $content);
        }

        $this->_content[] = $content;

        return $this;
    }

    /**
     * setDefaultDecoratorOptions()
     *
     * @param array $decoratorOptions
     * @param bool $mergeIntoExisting
     * @return Zend_Tool_Framework_Client_Response
     */
    public function setDefaultDecoratorOptions(Array $decoratorOptions, $mergeIntoExisting = false)
    {
        if ($mergeIntoExisting == false) {
            $this->_defaultDecoratorOptions = array();
        }

        $this->_defaultDecoratorOptions = array_merge($this->_defaultDecoratorOptions, $decoratorOptions);
        return $this;
    }

    /**
     * getContent()
     *
     * @return string
     */
    public function getContent()
    {
        return implode('', $this->_content);
    }

    /**
     * isException()
     *
     * @return bool
     */
    public function isException()
    {
        return isset($this->_exception);
    }

    /**
     * setException()
     *
     * @param Exception $exception
     * @return Zend_Tool_Framework_Client_Response
     */
    public function setException(Exception $exception)
    {
        $this->_exception = $exception;
        return $this;
    }

    /**
     * getException()
     *
     * @return Exception
     */
    public function getException()
    {
        return $this->_exception;
    }

    /**
     * Add Content Decorator
     *
     * @param Zend_Tool_Framework_Client_Response_ContentDecorator_Interface $contentDecorator
     * @return unknown
     */
    public function addContentDecorator(Zend_Tool_Framework_Client_Response_ContentDecorator_Interface $contentDecorator)
    {
        $decoratorName = strtolower($contentDecorator->getName());
        $this->_decorators[$decoratorName] = $contentDecorator;
        return $this;
    }

    /**
     * getContentDecorators()
     *
     * @return array
     */
    public function getContentDecorators()
    {
        return $this->_decorators;
    }

    /**
     * __toString() to cast to a string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) implode('', $this->_content);
    }

    /**
     * _applyDecorators() apply a group of decorators
     *
     * @param string $content
     * @param array $decoratorOptions
     * @return string
     */
    protected function _applyDecorators($content, Array $decoratorOptions)
    {
        $options = array_merge($this->_defaultDecoratorOptions, $decoratorOptions);

        $options = array_change_key_case($options, CASE_LOWER);

        if ($options) {
            foreach ($this->_decorators as $decoratorName => $decorator) {
                if (array_key_exists($decoratorName, $options)) {
                    $content = $decorator->decorate($content, $options[$decoratorName]);
                }
            }
        }

        return $content;

    }

}
