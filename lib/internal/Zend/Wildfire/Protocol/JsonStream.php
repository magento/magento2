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
 * @subpackage Protocol
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: JsonStream.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Wildfire_Plugin_Interface */
#require_once 'Zend/Wildfire/Plugin/Interface.php';

/** Zend_Wildfire_Channel_Interface */
#require_once 'Zend/Wildfire/Channel/Interface.php';

/** Zend_Json */
#require_once 'Zend/Json.php';

/**
 * Encodes messages into the Wildfire JSON Stream Communication Protocol.
 *
 * @category   Zend
 * @package    Zend_Wildfire
 * @subpackage Protocol
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Wildfire_Protocol_JsonStream
{
    /**
     * The protocol URI for this protocol
     */
    const PROTOCOL_URI = 'http://meta.wildfirehq.org/Protocol/JsonStream/0.2';

    /**
     * All messages to be sent.
     * @var array
     */
    protected $_messages = array();

    /**
     * Plugins that are using this protocol
     * @var array
     */
    protected $_plugins = array();

    /**
     * Register a plugin that uses this protocol
     *
     * @param Zend_Wildfire_Plugin_Interface $plugin The plugin to be registered
     * @return boolean Returns TRUE if plugin was registered, false if it was already registered
     */
    public function registerPlugin(Zend_Wildfire_Plugin_Interface $plugin)
    {
        if (in_array($plugin,$this->_plugins)) {
            return false;
        }
        $this->_plugins[] = $plugin;
        return true;
    }

    /**
     * Record a message with the given data in the given structure
     *
     * @param Zend_Wildfire_Plugin_Interface $plugin The plugin recording the message
     * @param string $structure The structure to be used for the data
     * @param array $data The data to be recorded
     * @return boolean Returns TRUE if message was recorded
     */
    public function recordMessage(Zend_Wildfire_Plugin_Interface $plugin, $structure, $data)
    {
        if(!isset($this->_messages[$structure])) {
            $this->_messages[$structure] = array();
        }

        $uri = $plugin->getUri();

        if(!isset($this->_messages[$structure][$uri])) {
            $this->_messages[$structure][$uri] = array();
        }

        $this->_messages[$structure][$uri][] = $this->_encode($data);
        return true;
    }

    /**
     * Remove all qued messages
     *
     * @param Zend_Wildfire_Plugin_Interface $plugin The plugin for which to clear messages
     * @return boolean Returns TRUE if messages were present
     */
    public function clearMessages(Zend_Wildfire_Plugin_Interface $plugin)
    {
        $uri = $plugin->getUri();

        $present = false;
        foreach ($this->_messages as $structure => $messages) {

            if(!isset($this->_messages[$structure][$uri])) {
                continue;
            }

            $present = true;

            unset($this->_messages[$structure][$uri]);

            if (!$this->_messages[$structure]) {
                unset($this->_messages[$structure]);
            }
        }
        return $present;
    }

    /**
     * Get all qued messages
     *
     * @return mixed Returns qued messages or FALSE if no messages are qued
     */
    public function getMessages()
    {
        if (!$this->_messages) {
            return false;
        }
        return $this->_messages;
    }

    /**
     * Use the JSON encoding scheme for the value specified
     *
     * @param mixed $value The value to be encoded
     * @return string  The encoded value
     */
    protected function _encode($value)
    {
        return Zend_Json::encode($value, true, array('silenceCyclicalExceptions'=>true));
    }

    /**
     * Retrieves all formatted data ready to be sent by the channel.
     *
     * @param Zend_Wildfire_Channel_Interface $channel The instance of the channel that will be transmitting the data
     * @return mixed Returns the data to be sent by the channel.
     * @throws Zend_Wildfire_Exception
     */
    public function getPayload(Zend_Wildfire_Channel_Interface $channel)
    {
        if (!$channel instanceof Zend_Wildfire_Channel_HttpHeaders) {
            #require_once 'Zend/Wildfire/Exception.php';
            throw new Zend_Wildfire_Exception('The '.get_class($channel).' channel is not supported by the '.get_class($this).' protocol.');
        }

        if ($this->_plugins) {
            foreach ($this->_plugins as $plugin) {
                $plugin->flushMessages(self::PROTOCOL_URI);
            }
        }

        if (!$this->_messages) {
            return false;
        }

        $protocol_index = 1;
        $structure_index = 1;
        $plugin_index = 1;
        $message_index = 1;

        $payload = array();

        $payload[] = array('Protocol-'.$protocol_index, self::PROTOCOL_URI);

        foreach ($this->_messages as $structure_uri => $plugin_messages ) {

            $payload[] = array($protocol_index.'-Structure-'.$structure_index, $structure_uri);

            foreach ($plugin_messages as $plugin_uri => $messages ) {

                $payload[] = array($protocol_index.'-Plugin-'.$plugin_index, $plugin_uri);

                foreach ($messages as $message) {

                    $parts = explode("\n",chunk_split($message, 5000, "\n"));

                    for ($i=0 ; $i<count($parts) ; $i++) {

                        $part = $parts[$i];
                        if ($part) {

                            $msg = '';

                            if (count($parts)>2) {
                                $msg = (($i==0)?strlen($message):'')
                                       . '|' . $part . '|'
                                       . (($i<count($parts)-2)?'\\':'');
                            } else {
                                $msg = strlen($part) . '|' . $part . '|';
                            }

                            $payload[] = array($protocol_index . '-'
                                               . $structure_index . '-'
                                               . $plugin_index . '-'
                                               . $message_index,
                                               $msg);

                            $message_index++;

                            if ($message_index > 99999) {
                                #require_once 'Zend/Wildfire/Exception.php';
                                throw new Zend_Wildfire_Exception('Maximum number (99,999) of messages reached!');
                            }
                        }
                    }
                }
                $plugin_index++;
            }
            $structure_index++;
        }

        return $payload;
    }

}

