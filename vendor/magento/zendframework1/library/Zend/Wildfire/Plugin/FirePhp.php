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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Controller_Request_Abstract */
#require_once('Zend/Controller/Request/Abstract.php');

/** Zend_Controller_Response_Abstract */
#require_once('Zend/Controller/Response/Abstract.php');

/** Zend_Wildfire_Channel_HttpHeaders */
#require_once 'Zend/Wildfire/Channel/HttpHeaders.php';

/** Zend_Wildfire_Protocol_JsonStream */
#require_once 'Zend/Wildfire/Protocol/JsonStream.php';

/** Zend_Wildfire_Plugin_Interface */
#require_once 'Zend/Wildfire/Plugin/Interface.php';

/**
 * Primary class for communicating with the FirePHP Firefox Extension.
 *
 * @category   Zend
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Wildfire_Plugin_FirePhp implements Zend_Wildfire_Plugin_Interface
{
    /**
     * Plain log style.
     */
    const LOG = 'LOG';

    /**
     * Information style.
     */
    const INFO = 'INFO';

    /**
     * Warning style.
     */
    const WARN = 'WARN';

    /**
     * Error style that increments Firebug's error counter.
     */
    const ERROR = 'ERROR';

    /**
     * Trace style showing message and expandable full stack trace.
     */
    const TRACE = 'TRACE';

    /**
     * Exception style showing message and expandable full stack trace.
     * Also increments Firebug's error counter.
     */
    const EXCEPTION = 'EXCEPTION';

    /**
     * Table style showing summary line and expandable table
     */
    const TABLE = 'TABLE';

    /**
     * Dump variable to Server panel in Firebug Request Inspector
     */
    const DUMP = 'DUMP';

    /**
     * Start a group in the Firebug Console
     */
    const GROUP_START = 'GROUP_START';

    /**
     * End a group in the Firebug Console
     */
    const GROUP_END = 'GROUP_END';

    /**
     * The plugin URI for this plugin
     */
    const PLUGIN_URI = 'http://meta.firephp.org/Wildfire/Plugin/ZendFramework/FirePHP/1.6.2';

    /**
     * The protocol URI for this plugin
     */
    const PROTOCOL_URI = Zend_Wildfire_Protocol_JsonStream::PROTOCOL_URI;

    /**
     * The structure URI for the Dump structure
     */
    const STRUCTURE_URI_DUMP = 'http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1';

    /**
     * The structure URI for the Firebug Console structure
     */
    const STRUCTURE_URI_FIREBUGCONSOLE = 'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1';

    /**
     * Singleton instance
     * @var Zend_Wildfire_Plugin_FirePhp
     */
    protected static $_instance = null;

    /**
     * Flag indicating whether FirePHP should send messages to the user-agent.
     * @var boolean
     */
    protected $_enabled = true;

    /**
     * The channel via which to send the encoded messages.
     * @var Zend_Wildfire_Channel_Interface
     */
    protected $_channel = null;

    /**
     * Messages that are buffered to be sent when protocol flushes
     * @var array
     */
    protected $_messages = array();

    /**
     * Options for the object
     * @var array
     */
    protected $_options = array(
        'traceOffset' => 1, /* The offset in the trace which identifies the source of the message */
        'maxTraceDepth' => 99, /* Maximum depth for stack traces */
        'maxObjectDepth' => 10, /* The maximum depth to traverse objects when encoding */
        'maxArrayDepth' => 20, /* The maximum depth to traverse nested arrays when encoding */
        'includeLineNumbers' => true /* Whether to include line and file info for each message */
    );

    /**
     * Filters used to exclude object members when encoding
     * @var array
     */
    protected $_objectFilters = array();

    /**
     * A stack of objects used during encoding to detect recursion
     * @var array
     */
    protected $_objectStack = array();

    /**
     * Create singleton instance.
     *
     * @param string $class OPTIONAL Subclass of Zend_Wildfire_Plugin_FirePhp
     * @return Zend_Wildfire_Plugin_FirePhp Returns the singleton Zend_Wildfire_Plugin_FirePhp instance
     * @throws Zend_Wildfire_Exception
     */
    public static function init($class = null)
    {
        if (self::$_instance !== null) {
            #require_once 'Zend/Wildfire/Exception.php';
            throw new Zend_Wildfire_Exception('Singleton instance of Zend_Wildfire_Plugin_FirePhp already exists!');
        }
        if ($class !== null) {
            if (!is_string($class)) {
                #require_once 'Zend/Wildfire/Exception.php';
                throw new Zend_Wildfire_Exception('Third argument is not a class string');
            }

            if (!class_exists($class)) {
                #require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($class);
            }
            self::$_instance = new $class();
            if (!self::$_instance instanceof Zend_Wildfire_Plugin_FirePhp) {
                self::$_instance = null;
                #require_once 'Zend/Wildfire/Exception.php';
                throw new Zend_Wildfire_Exception('Invalid class to third argument. Must be subclass of Zend_Wildfire_Plugin_FirePhp.');
            }
        } else {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Constructor
     * @return void
     */
    protected function __construct()
    {
        $this->_channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $this->_channel->getProtocol(self::PROTOCOL_URI)->registerPlugin($this);
    }

    /**
     * Get or create singleton instance
     *
     * @param bool $skipCreate True if an instance should not be created
     * @return Zend_Wildfire_Plugin_FirePhp
     */
    public static function getInstance($skipCreate=false)
    {
        if (self::$_instance===null && $skipCreate!==true) {
            return self::init();
        }
        return self::$_instance;
    }

    /**
     * Destroys the singleton instance
     *
     * Primarily used for testing.
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$_instance = null;
    }

    /**
     * Enable or disable sending of messages to user-agent.
     * If disabled all headers to be sent will be removed.
     *
     * @param boolean $enabled Set to TRUE to enable sending of messages.
     * @return boolean The previous value.
     */
    public function setEnabled($enabled)
    {
        $previous = $this->_enabled;
        $this->_enabled = $enabled;
        if (!$this->_enabled) {
            $this->_messages = array();
            $this->_channel->getProtocol(self::PROTOCOL_URI)->clearMessages($this);
        }
        return $previous;
    }

    /**
     * Determine if logging to user-agent is enabled.
     *
     * @return boolean Returns TRUE if logging is enabled.
     */
    public function getEnabled()
    {
        return $this->_enabled;
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
      if (!array_key_exists($key,$this->_options)) {
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
      if (!array_key_exists($key,$this->_options)) {
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

    /**
     * Specify a filter to be used when encoding an object
     *
     * Filters are used to exclude object members.
     *
     * @param string $Class The class name of the object
     * @param array $Filter An array of members to exclude
     * @return void
     */
    public function setObjectFilter($class, $filter) {
      $this->_objectFilters[$class] = $filter;
    }

    /**
     * Starts a group in the Firebug Console
     *
     * @param string $title The title of the group
     * @param array $options OPTIONAL Setting 'Collapsed' to true will initialize group collapsed instead of expanded
     * @return TRUE if the group instruction was added to the response headers or buffered.
     */
    public static function group($title, $options=array())
    {
        return self::send(null, $title, self::GROUP_START, $options);
    }

    /**
     * Ends a group in the Firebug Console
     *
     * @return TRUE if the group instruction was added to the response headers or buffered.
     */
    public static function groupEnd()
    {
        return self::send(null, null, self::GROUP_END);
    }

    /**
     * Logs variables to the Firebug Console
     * via HTTP response headers and the FirePHP Firefox Extension.
     *
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @param  string  $style  OPTIONAL Style of the log event.
     * @param  array  $options OPTIONAL Options to change how messages are processed and sent
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     * @throws Zend_Wildfire_Exception
     */
    public static function send($var, $label=null, $style=null, $options=array())
    {
        $firephp = self::getInstance();

        if (!$firephp->getEnabled()) {
            return false;
        }

        if ($var instanceof Zend_Wildfire_Plugin_FirePhp_Message) {

            if ($var->getBuffered()) {
                if (!in_array($var, self::$_instance->_messages)) {
                    self::$_instance->_messages[] = $var;
                }
                return true;
            }

            if ($var->getDestroy()) {
                return false;
            }

            $style = $var->getStyle();
            $label = $var->getLabel();
            $options = $var->getOptions();
            $var = $var->getMessage();
        }

        if (!self::$_instance->_channel->isReady()) {
            return false;
        }

        foreach ($options as $name => $value) {
            if ($value===null) {
                unset($options[$name]);
            }
        }
        $options = array_merge($firephp->getOptions(), $options);

        $trace = null;

        $skipFinalEncode = false;

        $meta = array();
        $meta['Type'] = $style;

        if ($var instanceof Exception) {

            $eTrace = $var->getTrace();
            $eTrace = array_splice($eTrace, 0, $options['maxTraceDepth']);

            $var = array('Class'=>get_class($var),
                         'Message'=>$var->getMessage(),
                         'File'=>$var->getFile(),
                         'Line'=>$var->getLine(),
                         'Type'=>'throw',
                         'Trace'=>$firephp->_encodeTrace($eTrace));

            $meta['Type'] = self::EXCEPTION;

            $skipFinalEncode = true;

        } else
        if ($meta['Type']==self::TRACE) {

            if (!$label && $var) {
                $label = $var;
                $var = null;
            }

            if (!$trace) {
                $trace = $firephp->_getStackTrace(array_merge($options,
                                                              array('maxTraceDepth'=>$options['maxTraceDepth']+1)));
            }

            $var = array('Class'=>$trace[0]['class'],
                         'Type'=>$trace[0]['type'],
                         'Function'=>$trace[0]['function'],
                         'Message'=>$label,
                         'File'=>isset($trace[0]['file'])?$trace[0]['file']:'',
                         'Line'=>isset($trace[0]['line'])?$trace[0]['line']:'',
                         'Args'=>isset($trace[0]['args'])?$firephp->_encodeObject($trace[0]['args']):'',
                         'Trace'=>$firephp->_encodeTrace(array_splice($trace,1)));

          $skipFinalEncode = true;

        } else
        if ($meta['Type']==self::TABLE) {

          $var = $firephp->_encodeTable($var);

          $skipFinalEncode = true;

        } else {
            if ($meta['Type']===null) {
                $meta['Type'] = self::LOG;
            }
        }

        if ($label!=null) {
            $meta['Label'] = $label;
        }

        switch ($meta['Type']) {
            case self::LOG:
            case self::INFO:
            case self::WARN:
            case self::ERROR:
            case self::EXCEPTION:
            case self::TRACE:
            case self::TABLE:
            case self::DUMP:
            case self::GROUP_START:
            case self::GROUP_END:
                break;
            default:
                #require_once 'Zend/Wildfire/Exception.php';
                throw new Zend_Wildfire_Exception('Log style "'.$meta['Type'].'" not recognized!');
                break;
        }

        if ($meta['Type'] != self::DUMP && $options['includeLineNumbers']) {
            if (!isset($meta['File']) || !isset($meta['Line'])) {

                if (!$trace) {
                    $trace = $firephp->_getStackTrace(array_merge($options,
                                                                  array('maxTraceDepth'=>$options['maxTraceDepth']+1)));
                }

                $meta['File'] = isset($trace[0]['file'])?$trace[0]['file']:'';
                $meta['Line'] = isset($trace[0]['line'])?$trace[0]['line']:'';

            }
        } else {
            unset($meta['File']);
            unset($meta['Line']);
        }

        if ($meta['Type'] == self::GROUP_START) {
            if (isset($options['Collapsed'])) {
                $meta['Collapsed'] = ($options['Collapsed'])?'true':'false';
            }
        }

        if ($meta['Type'] == self::DUMP) {

          return $firephp->_recordMessage(self::STRUCTURE_URI_DUMP,
                                          array('key'=>$meta['Label'],
                                                'data'=>$var),
                                          $skipFinalEncode);

        } else {

          return $firephp->_recordMessage(self::STRUCTURE_URI_FIREBUGCONSOLE,
                                          array('data'=>$var,
                                                'meta'=>$meta),
                                          $skipFinalEncode);
        }
    }

    /**
     * Gets a stack trace
     *
     * @param array $options Options to change how the stack trace is returned
     * @return array The stack trace
     */
    protected function _getStackTrace($options)
    {
        $trace = debug_backtrace();

        $trace = array_splice($trace, $options['traceOffset']);

        if (!count($trace)) {
            return $trace;
        }

        if (isset($options['fixZendLogOffsetIfApplicable']) && $options['fixZendLogOffsetIfApplicable']) {
            if (count($trace) >=3 &&
                isset($trace[0]['file']) && substr($trace[0]['file'], -7, 7)=='Log.php' &&
                isset($trace[1]['function']) && $trace[1]['function']=='__call') {

                $trace = array_splice($trace, 2);
            }
        }

        return array_splice($trace, 0, $options['maxTraceDepth']);
    }

    /**
     * Record a message with the given data in the given structure
     *
     * @param string $structure The structure to be used for the data
     * @param array $data The data to be recorded
     * @param boolean $skipEncode TRUE if variable encoding should be skipped
     * @return boolean Returns TRUE if message was recorded
     * @throws Zend_Wildfire_Exception
     */
    protected function _recordMessage($structure, $data, $skipEncode=false)
    {
        switch($structure) {

            case self::STRUCTURE_URI_DUMP:

                if (!isset($data['key'])) {
                    #require_once 'Zend/Wildfire/Exception.php';
                    throw new Zend_Wildfire_Exception('You must supply a key.');
                }
                if (!array_key_exists('data',$data)) {
                    #require_once 'Zend/Wildfire/Exception.php';
                    throw new Zend_Wildfire_Exception('You must supply data.');
                }

                $value = $data['data'];
                if (!$skipEncode) {
                  $value = $this->_encodeObject($data['data']);
                }

                return $this->_channel->getProtocol(self::PROTOCOL_URI)->
                           recordMessage($this,
                                         $structure,
                                         array($data['key']=>$value));

            case self::STRUCTURE_URI_FIREBUGCONSOLE:

                if (!isset($data['meta']) ||
                    !is_array($data['meta']) ||
                    !array_key_exists('Type',$data['meta'])) {

                    #require_once 'Zend/Wildfire/Exception.php';
                    throw new Zend_Wildfire_Exception('You must supply a "Type" in the meta information.');
                }
                if (!array_key_exists('data',$data)) {
                    #require_once 'Zend/Wildfire/Exception.php';
                    throw new Zend_Wildfire_Exception('You must supply data.');
                }

                $value = $data['data'];
                if (!$skipEncode) {
                  $value = $this->_encodeObject($data['data']);
                }

                return $this->_channel->getProtocol(self::PROTOCOL_URI)->
                           recordMessage($this,
                                         $structure,
                                         array($data['meta'],
                                               $value));

            default:
                #require_once 'Zend/Wildfire/Exception.php';
                throw new Zend_Wildfire_Exception('Structure of name "'.$structure.'" is not recognized.');
                break;
        }
        return false;
    }

    /**
     * Encodes a table by encoding each row and column with _encodeObject()
     *
     * @param array $Table The table to be encoded
     * @return array
     */
    protected function _encodeTable($table)
    {
      if (!$table) {
          return $table;
      }
      for ($i=0 ; $i<count($table) ; $i++) {
          if (is_array($table[$i])) {
              for ($j=0 ; $j<count($table[$i]) ; $j++) {
                  $table[$i][$j] = $this->_encodeObject($table[$i][$j]);
              }
          }
        }
      return $table;
    }

    /**
     * Encodes a trace by encoding all "args" with _encodeObject()
     *
     * @param array $Trace The trace to be encoded
     * @return array The encoded trace
     */
    protected function _encodeTrace($trace)
    {
      if (!$trace) {
          return $trace;
      }
      for ($i=0 ; $i<sizeof($trace) ; $i++) {
          if (isset($trace[$i]['args'])) {
              $trace[$i]['args'] = $this->_encodeObject($trace[$i]['args']);
          }
      }
      return $trace;
    }

    /**
     * Encode an object by generating an array containing all object members.
     *
     * All private and protected members are included. Some meta info about
     * the object class is added.
     *
     * @param mixed $object The object/array/value to be encoded
     * @return array The encoded object
     */
    protected function _encodeObject($object, $objectDepth = 1, $arrayDepth = 1)
    {
        $return = array();

        if (is_resource($object)) {

            return '** '.(string)$object.' **';

        } else
        if (is_object($object)) {

            if ($objectDepth > $this->_options['maxObjectDepth']) {
              return '** Max Object Depth ('.$this->_options['maxObjectDepth'].') **';
            }

            foreach ($this->_objectStack as $refVal) {
                if ($refVal === $object) {
                    return '** Recursion ('.get_class($object).') **';
                }
            }
            array_push($this->_objectStack, $object);

            $return['__className'] = $class = get_class($object);

            $reflectionClass = new ReflectionClass($class);
            $properties = array();
            foreach ( $reflectionClass->getProperties() as $property) {
                $properties[$property->getName()] = $property;
            }

            $members = (array)$object;

            foreach ($properties as $just_name => $property) {

                $name = $raw_name = $just_name;

                if ($property->isStatic()) {
                    $name = 'static:'.$name;
                }
                if ($property->isPublic()) {
                    $name = 'public:'.$name;
                } else
                if ($property->isPrivate()) {
                    $name = 'private:'.$name;
                    $raw_name = "\0".$class."\0".$raw_name;
                } else
                if ($property->isProtected()) {
                    $name = 'protected:'.$name;
                    $raw_name = "\0".'*'."\0".$raw_name;
                }

                if (!(isset($this->_objectFilters[$class])
                      && is_array($this->_objectFilters[$class])
                      && in_array($just_name,$this->_objectFilters[$class]))) {

                    if (array_key_exists($raw_name,$members)
                        && !$property->isStatic()) {

                        $return[$name] = $this->_encodeObject($members[$raw_name], $objectDepth + 1, 1);

                    } else {
                        if (method_exists($property,'setAccessible')) {
                            $property->setAccessible(true);
                            $return[$name] = $this->_encodeObject($property->getValue($object), $objectDepth + 1, 1);
                        } else
                        if ($property->isPublic()) {
                            $return[$name] = $this->_encodeObject($property->getValue($object), $objectDepth + 1, 1);
                        } else {
                            $return[$name] = '** Need PHP 5.3 to get value **';
                        }
                    }
                } else {
                  $return[$name] = '** Excluded by Filter **';
                }
            }

            // Include all members that are not defined in the class
            // but exist in the object
            foreach($members as $just_name => $value) {

                $name = $raw_name = $just_name;

                if ($name{0} == "\0") {
                    $parts = explode("\0", $name);
                    $name = $parts[2];
                }
                if (!isset($properties[$name])) {
                    $name = 'undeclared:'.$name;

                    if (!(isset($this->objectFilters[$class])
                          && is_array($this->objectFilters[$class])
                          && in_array($just_name,$this->objectFilters[$class]))) {

                      $return[$name] = $this->_encodeObject($value, $objectDepth + 1, 1);
                    } else {
                      $return[$name] = '** Excluded by Filter **';
                    }
                }
            }

            array_pop($this->_objectStack);

        } elseif (is_array($object)) {

            if ($arrayDepth > $this->_options['maxArrayDepth']) {
              return '** Max Array Depth ('.$this->_options['maxArrayDepth'].') **';
            }

            foreach ($object as $key => $val) {

              // Encoding the $GLOBALS PHP array causes an infinite loop
              // if the recursion is not reset here as it contains
              // a reference to itself. This is the only way I have come up
              // with to stop infinite recursion in this case.
              if ($key=='GLOBALS'
                  && is_array($val)
                  && array_key_exists('GLOBALS',$val)) {

                  $val['GLOBALS'] = '** Recursion (GLOBALS) **';
              }
              $return[$key] = $this->_encodeObject($val, 1, $arrayDepth + 1);
            }
        } else {
            return $object;
        }
        return $return;
    }

    /*
     * Zend_Wildfire_Plugin_Interface
     */

    /**
     * Get the unique indentifier for this plugin.
     *
     * @return string Returns the URI of the plugin.
     */
    public function getUri()
    {
        return self::PLUGIN_URI;
    }

    /**
     * Flush any buffered data.
     *
     * @param string $protocolUri The URI of the protocol that should be flushed to
     * @return void
     */
    public function flushMessages($protocolUri)
    {
        if (!$this->_messages || $protocolUri!=self::PROTOCOL_URI) {
            return;
        }

        foreach( $this->_messages as $message ) {
            if (!$message->getDestroy()) {
                $this->send($message->getMessage(),
                            $message->getLabel(),
                            $message->getStyle(),
                            $message->getOptions());
            }
        }

        $this->_messages = array();
    }
}
