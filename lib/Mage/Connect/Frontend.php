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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Connect_Frontend
{

    /**
     * Silent flag. If set no output is produced to view.
     * Should be used in derived classes.
     * @var bool
     */
    protected $_silent = false;

    /**
     * Capture mode. If set command output should be collected
     * by derived class impplementation
     * @var bool
     */
    protected $_capture = false;


    /**
     * push/pop variable for capture
     * @var array
     */
    protected $_captureSaved = array();

    /**
     * push/pop variable for silent
     * @var array
     */
    protected $_silentSaved = array();

    /**
     * Errors list
     * @var array
     */
    protected $_errors = array();

    /**
     * Add error to errors list
     * @param mixed $data
     * @return void
     */
    public function addError($data)
    {
        $this->_errors[] = $data;
    }

    /**
     * Get errors, clear errors list with first param
     * @param bool $clear
     * @return array
     */
    public function getErrors($clear = true)
    {
        if(!$clear) {
            return $this->_errors;
        }
        $out = $this->_errors;
        $this->clearErrors();
        return $out;
    }

    /**
     * Clear errors array
     * @return void
     */
    public function clearErrors()
    {
        $this->_errors = array();
    }

    /**
     * Are there any errros?
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->_errors) != 0;
    }

    /**
     * Error processing
     * @param string $command
     * @param stting $message
     * @return void
     */
    public function doError($command, $message)
    {
        $this->addError(array($command, $message));
    }

    /**
     * Save capture state
     * @return Mage_Connect_Frontend
     */
    public function pushCapture()
    {
        array_push($this->_captureSaved, $this->_capture);
        return $this;
    }

    /**
     * Restore capture state
     * @return Mage_Connect_Frontend
     */
    public function popCapture()
    {
        $this->_capture = array_pop($this->_captureSaved);
        return $this;
    }

    /**
     * Set capture mode
     * @param bool $arg true by default
     * @return Mage_Connect_Frontend
     */
    public function setCapture($arg = true)
    {
        $this->_capture = $arg;
        return $this;
    }

    /**
     * Getter for capture mode
     * @return bool
     */
    public function isCapture()
    {
        return $this->_capture;
    }

    /**
     * Log stub
     * @param $msg
     * @return
     */
    public function log($msg)
    {

    }

    /**
     * Ouptut method
     * @param array $data
     * @return void
     */
    public function output($data)
    {

    }

    /**
     * Get instance of derived class
     *
     * @param $class CLI for example will produce Mage_Connect_Frontend_CLI
     * @return object
     */
    public static function getInstance($class)
    {
        $class = __CLASS__."_".$class;
        return new $class();
    }

    /**
     * Get output if capture mode set
     * Clear prevoius if needed
     * @param bool $clearPrevious
     * @return mixed
     */
    public function getOutput($clearPrevious = true)
    {

    }


    /**
     * Save silent mode
     * @return Mage_Connect_Frontend
     */
    public function pushSilent()
    {
        array_push($this->_silentSaved, $this->_silent);
        return $this;
    }

    /**
     * Restore silent mode
     * @return Mage_Connect_Frontend
     */
    public function popSilent()
    {
        $this->_silent = array_pop($this->_silentSaved);
        return $this;
    }

    /**
     * Set silent mode
     * @param bool $value
     * @return Mage_Connect_Frontend
     */
    public function setSilent($value = true)
    {
        $this->_silent = (bool) $value;
        return $this;
    }

    /**
     * Is silent mode?
     * @return bool
     */
    public function isSilent()
    {
        return (bool) $this->_silent;
    }

    /**
    * Method for ask client about rewrite all files.
    *
    * @param $string
    */
    public function confirm($string)
    {
        
    }
}

