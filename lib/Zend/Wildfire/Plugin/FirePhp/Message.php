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
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Message.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * A message envelope that can be passed to Zend_Wildfire_Plugin_FirePhp to be
 * logged to Firebug instead of a variable.
 *
 * @category   Zend
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Wildfire_Plugin_FirePhp_Message
{
    /**
     * The style of the message
     * @var string
     */
    protected $_style = null;

    /**
     * The label of the message
     * @var string
     */
    protected $_label = null;

    /**
     * The message value
     * @var mixed
     */
    protected $_message = null;

    /**
     * Flag indicating if message buffering is enabled
     * @var boolean
     */
    protected $_buffered = false;

    /**
     * Flag indicating if message should be destroyed and not delivered
     * @var boolean
     */
    protected $_destroy = false;

    /**
     * Random unique ID used to identify message in comparison operations
     * @var string
     */
    protected $_ruid = false;

    /**
     * Options for the object
     * @var array
     */
    protected $_options = array(
        'traceOffset' => null, /* The offset in the trace which identifies the source of the message */
        'includeLineNumbers' => null /* Whether to include line and file info for this message */
    );

    /**
     * Creates a new message with the given style and message
     *
     * @param string $style Style of the message.
     * @param mixed $message The message
     * @return void
     */
    function __construct($style, $message)
    {
        $this->_style = $style;
        $this->_message = $message;
        $this->_ruid = md5(microtime().mt_rand());
    }

    /**
     * Set the label of the message
     *
     * @param string $label The label to be set
     * @return void
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }

    /**
     * Get the label of the message
     *
     * @return string The label of the message
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Enable or disable message buffering
     *
     * If a message is buffered it can be updated for the duration of the
     * request and is only flushed at the end of the request.
     *
     * @param boolean $buffered TRUE to enable buffering FALSE otherwise
     * @return boolean Returns previous buffering value
     */
    public function setBuffered($buffered)
    {
        $previous = $this->_buffered;
        $this->_buffered = $buffered;
        return $previous;
    }

    /**
     * Determine if buffering is enabled or disabled
     *
     * @return boolean Returns TRUE if buffering is enabled, FALSE otherwise.
     */
    public function getBuffered()
    {
        return $this->_buffered;
    }

    /**
     * Destroy the message to prevent delivery
     *
     * @param boolean $destroy TRUE to destroy FALSE otherwise
     * @return boolean Returns previous destroy value
     */
    public function setDestroy($destroy)
    {
        $previous = $this->_destroy;
        $this->_destroy = $destroy;
        return $previous;
    }

    /**
     * Determine if message should be destroyed
     *
     * @return boolean Returns TRUE if message should be destroyed, FALSE otherwise.
     */
    public function getDestroy()
    {
        return $this->_destroy;
    }

    /**
     * Set the style of the message
     *
     * @return void
     */
    public function setStyle($style)
    {
        $this->_style = $style;
    }

    /**
     * Get the style of the message
     *
     * @return string The style of the message
     */
    public function getStyle()
    {
        return $this->_style;
    }

    /**
     * Set the actual message to be sent in its final format.
     *
     * @return void
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * Get the actual message to be sent in its final format.
     *
     * @return mixed Returns the message to be sent.
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Set a single option
     *
     * @param  string $key The name of the option
     * @param  mixed $value The value of the option
     * @return mixed The previous value of the option
     */
    public function setOption($key, $value)
    {
      if(!array_key_exists($key,$this->_options)) {
        throw new Zend_Wildfire_Exception('Option with name "'.$key.'" does not exist!');
      }
      $previous = $this->_options[$key];
      $this->_options[$key] = $value;
      return $previous;
    }

    /**
     * Retrieve a single option
     *
     * @param  string $key The name of the option
     * @return mixed The value of the option
     */
    public function getOption($key)
    {
      if(!array_key_exists($key,$this->_options)) {
        throw new Zend_Wildfire_Exception('Option with name "'.$key.'" does not exist!');
      }
      return $this->_options[$key];
    }

    /**
     * Retrieve all options
     *
     * @return array All options
     */
    public function getOptions()
    {
      return $this->_options;
    }
}

